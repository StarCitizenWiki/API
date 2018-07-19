<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationEmail;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NotificationController
 */
class NotificationController extends Controller
{
    const ADMIN_NOTIFICATION_INDEX = 'web.admin.notifications.index';
    const MESSAGE = 'message';
    const NOTIFICATION = 'Notification';

    private $jobDelay = null;

    /**
     * ShortUrlController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.notifications.index')->with(
            'notifications',
            Notification::withTrashed()->orderByDesc('created_at')->simplePaginate(100)
        );
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.notifications.create');
    }

    /**
     * @param \App\Models\Api\Notification $notification
     *
     * @return \Illuminate\View\View
     */
    public function edit(Notification $notification)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['id' => $notification->id]);

        return view('admin.notifications.edit')->with(
            'notification',
            $notification
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function store(Request $request)
    {
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

        $this->dispatchJob($notification);

        return redirect()->route('web.admin.dashboard')->with(
            self::MESSAGE,
            __('crud.created', ['type' => self::NOTIFICATION])
        );
    }

    /**
     * @param array $data
     */
    private function processOutput(array &$data)
    {
        $outputs = [
            'output_status' => false,
            'output_mail' => false,
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
        if ($request->has('delete')) {
            return $this->destroy($notification);
        }

        if ($request->has('restore')) {
            return $this->restore($notification);
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
                'resend_mail' => 'nullable',
            ]
        );

        $this->processOutput($data);

        $data['expired_at'] = Carbon::parse($request->get('expired_at'));
        $this->processPublishedAt($data);

        $resendEmail = array_pull($data, 'resend_email', false);
        $notification->update($data);

        if ('resend_email' === $resendEmail || ($notification->output_email === false && $data['output_email'] === true)) {
            $this->dispatchJob($notification);
        }

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->with(
            self::MESSAGE,
            __('crud.updated', ['type' => self::NOTIFICATION])
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
        $notification->delete();

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->with(
            self::MESSAGE,
            __('crud.deleted', ['type' => self::NOTIFICATION])
        );
    }

    /**
     * @param \App\Models\Api\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Notification $notification)
    {
        $notification->restore();

        return redirect()->route(self::ADMIN_NOTIFICATION_INDEX)->with(
            self::MESSAGE,
            __('crud.restored', ['type' => self::NOTIFICATION])
        );
    }
}
