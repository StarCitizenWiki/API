<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationEmail;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NotificationController
 */
class NotificationController extends Controller
{
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
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showNotificationListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.notifications.index')->with(
            'notifications',
            Notification::withTrashed()->orderByDesc('created_at')->simplePaginate(100)
        );
    }

    /**
     * @return \Illuminate\View\View
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showAddNotificationView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.notifications.add');
    }

    /**
     * @param \App\Models\Notification $notification
     *
     * @return \Illuminate\View\View
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showEditNotificationView(Notification $notification)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $notification->id]);

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
    public function addNotification(Request $request)
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

        return redirect()->route('admin.dashboard')->with('message', __('crud.created', ['type' => 'Notification']));
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
     * @param \App\Models\Notification $notification
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function updateNotification(Request $request, Notification $notification)
    {
        if ($request->has('delete')) {
            return $this->deleteNotification($notification);
        }

        if ($request->has('restore')) {
            return $this->restoreNotification($notification);
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

        return redirect()->route('admin.notification.list')->with(
            'message',
            __('crud.updated', ['type' => 'Notification'])
        );
    }

    /**
     * @param \App\Models\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function deleteNotification(Notification $notification)
    {
        $type = 'message';
        $message = __('crud.deleted', ['type' => 'Notification']);

        $notification->delete();

        return redirect()->route('admin.notification.list')->with($type, $message);
    }

    /**
     * @param \App\Models\Notification $notification
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreNotification(Notification $notification)
    {
        $type = 'message';
        $message = __('crud.restored', ['type' => 'Notification']);

        $notification->restore();

        return redirect()->route('admin.notification.list')->with($type, $message);
    }
}
