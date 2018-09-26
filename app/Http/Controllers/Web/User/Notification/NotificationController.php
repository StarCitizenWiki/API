<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Notification;

use App\Http\Controllers\Controller;
use App\Jobs\Web\SendNotificationEmail;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NotificationController
 */
class NotificationController extends Controller
{
    private const ADMIN_NOTIFICATION_INDEX = 'web.user.notifications.index';
    private const NOTIFICATION = 'Benachrichtigung';

    private $jobDelay = null;

    /**
     * Notification Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.notifications.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.notifications.index',
            [
                'notifications' => Notification::orderByDesc('created_at')->simplePaginate(100),
            ]
        );
    }

    /**
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(): View
    {
        $this->authorize('web.user.notifications.create');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('user.notifications.create');
    }

    /**
     * @param \App\Models\Api\Notification $notification
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Notification $notification)
    {
        $this->authorize('web.user.notifications.update');
        app('Log')::debug(make_name_readable(__FUNCTION__), ['id' => $notification->id]);

        return view(
            'user.notifications.edit',
            [
                'notification' => $notification,
            ]
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('web.user.notifications.create');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $this->validate(
            $request,
            [
                'content' => 'required|string|min:5',
                'level' => 'required|int|between:0,3',
                'expired_at' => 'required|date|after:'.Carbon::now(),
                'published_at' => 'nullable|date',
                'order' => 'nullable|int',
                'output' => 'required|array|in:status,email,index',
            ]
        );

        $this->processOutput($data);
        $this->processPublishedAt($data);

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at'])->toDateTimeString();
        }

        if (!array_key_exists('order', $data) || is_null($data['order'])) {
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
     * @param \Illuminate\Http\Request     $request
     * @param \App\Models\Api\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function update(Request $request, Notification $notification)
    {
        $this->authorize('web.user.notifications.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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

        $resendEmail = (bool) array_pull($data, 'resend_email', false);

        if (($notification->output_email === false && $data['output_email'] === true && !$notification->expired()) || true === $resendEmail) {
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
     * @param \App\Models\Api\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('web.user.notifications.delete');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $notification->delete();

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->withMessages(
            [
                'danger' => [
                    __('crud.deleted', ['type' => self::NOTIFICATION]),
                ],
            ]
        );
    }

    /**
     * @param array $data
     */
    private function processOutput(array &$data)
    {
        $outputs = [
            'output_status' => false,
            'output_email' => false,
            'output_index' => false,
        ];

        foreach (array_pull($data, 'output') as $type) {
            $data['output_'.$type] = true;
        }

        $data = array_merge($outputs, $data);
    }

    /**
     * @param array $data
     */
    private function processPublishedAt(array &$data)
    {
        if (array_key_exists('published_at', $data) && !is_null($data['published_at'])) {
            $data['published_at'] = Carbon::parse($data['published_at'])->toDateTimeString();
            $this->jobDelay = Carbon::parse($data['published_at']);
        } else {
            $data['published_at'] = Carbon::now()->toDateTimeString();
        }
    }

    /**
     * @param \App\Models\Api\Notification $notification
     */
    private function dispatchJob(Notification $notification)
    {
        $job = (new SendNotificationEmail($notification));

        if (!is_null($this->jobDelay)) {
            $job->delay($this->jobDelay);
        }

        $this->dispatch($job);
    }
}
