<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Notification;

use App\Http\Controllers\Controller;
use App\Jobs\Web\SendNotificationEmail;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Class NotificationController
 */
class NotificationController extends Controller
{
    private const ADMIN_NOTIFICATION_INDEX = 'web.user.notifications.index';
    private const NOTIFICATION = 'Benachrichtigung';

    private $jobDelay;

    /**
     * Notification Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.notifications.view');

        return view(
            'user.notifications.index',
            [
                'notifications' => Notification::query()->orderByDesc('created_at')->simplePaginate(100),
            ]
        );
    }

    /**
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create(): View
    {
        $this->authorize('web.user.notifications.create');

        return view('user.notifications.create');
    }

    /**
     * @param Notification $notification
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Notification $notification): View
    {
        $this->authorize('web.user.notifications.update');

        return view(
            'user.notifications.edit',
            [
                'notification' => $notification,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return $this|Model
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->authorize('web.user.notifications.create');

        $data = $this->validate(
            $request,
            [
                'content' => 'required|string|min:5',
                'level' => 'required|int|between:0,3',
                'expired_at' => 'required|date|after:' . Carbon::now(),
                'published_at' => 'nullable|date',
                'order' => 'nullable|int',
                'output' => 'required|array|in:status,email,index',
            ]
        );

        $this->processOutput($data);
        $this->processPublishedAt($data);

        if ($data['expired_at'] !== null) {
            $data['expired_at'] = Carbon::parse($data['expired_at'])->toDateTimeString();
        }

        if (!array_key_exists('order', $data) || $data['order'] === null) {
            $data['order'] = 0;
        }

        $notification = Notification::create($data);

        if (true === $data['output_email']) {
            $this->dispatchJob($notification);
        }

        return redirect()->route('web.user.dashboard')->withMessages(
            [
                'success' => [
                    __('crud.created', ['type' => self::NOTIFICATION]),
                ],
            ]
        );
    }

    /**
     * @param array $data
     */
    private function processOutput(array &$data): void
    {
        $outputs = [
            'output_status' => false,
            'output_email' => false,
            'output_index' => false,
        ];

        foreach (Arr::pull($data, 'output') as $type) {
            $data['output_' . $type] = true;
        }

        $data = array_merge($outputs, $data);
    }

    /**
     * @param array $data
     */
    private function processPublishedAt(array &$data): void
    {
        if (array_key_exists('published_at', $data) && $data['published_at'] !== null) {
            $data['published_at'] = Carbon::parse($data['published_at'])->toDateTimeString();
            $this->jobDelay = Carbon::parse($data['published_at']);
        } else {
            $data['published_at'] = Carbon::now()->toDateTimeString();
        }
    }

    /**
     * @param Notification $notification
     */
    private function dispatchJob(Notification $notification): void
    {
        $job = (new SendNotificationEmail($notification));

        if ($this->jobDelay !== null) {
            $job->delay($this->jobDelay);
        }

        $this->dispatch($job);
    }

    /**
     * @param Request      $request
     * @param Notification $notification
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorize('web.user.notifications.update');

        if ($request->has('delete')) {
            return $this->destroy($notification);
        }

        $data = $this->validate(
            $request,
            [
                'content' => 'required|string|min:5',
                'level' => 'required|int|between:0,3',
                'expired_at' => 'required|date',
                'output' => 'required|array|in:status,email,index',
                'order' => 'required|int|between:0,5',
                'published_at' => 'required|date',
                'resend_email' => 'nullable|boolean',
            ]
        );

        $this->processOutput($data);

        $data['expired_at'] = Carbon::parse($request->get('expired_at'));
        $this->processPublishedAt($data);

        $resendEmail = (bool) Arr::pull($data, 'resend_email', false);

        if (
            true === $resendEmail || ($notification->output_email === false && $data['output_email'] === true && !$notification->expired(
            ))
        ) {
            $this->dispatchJob($notification);
        }

        $notification->update($data);

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => self::NOTIFICATION]),
                ],
            ]
        );
    }

    /**
     * @param Notification $notification
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $this->authorize('web.user.notifications.delete');

        $notification->delete();

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->withMessages(
            [
                'danger' => [
                    __('crud.deleted', ['type' => self::NOTIFICATION]),
                ],
            ]
        );
    }
}
