<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Events\NotificationCreated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NotificationController
 * @package App\Http\Controllers\Admin
 */
class NotificationController extends Controller
{
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
    public function showNotificationsListView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.notifications.index')->with(
            'notifications',
            Notification::withTrashed()->orderByDesc('created_at')->simplePaginate(100)
        );
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function showEditNotificationView(int $id)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $notification = Notification::withTrashed()->findOrFail($id);

            return view('admin.notifications.edit')->with(
                'notification',
                $notification
            );
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("Notification with ID: {$id} not found");
        }

        return redirect()->route('admin_notifications_list')->withErrors([__('crud.not_found', ['type' => 'Notification'])]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function addNotification(Request $request)
    {
        $this->validate(
            $request,
            [
                'content'      => 'required|string|min:5',
                'level'        => 'required|int|between:0,3',
                'expired_at'   => 'required|date|after:'.Carbon::now(),
                'published_at' => 'required|date',
                'output'       => 'required|array|in:status,email,index',
            ]
        );

        $data = $request->all();

        $outputType = array_pull($data, 'output');

        foreach ($outputType as $type) {
            $data['output_'.$type] = true;
        }

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at'])->toDateTimeString();
        }

        $data['level'] = Notification::NOTIFICATION_LEVEL_TYPES[$data['level']];

        $notification = Notification::create($data);

        event(new NotificationCreated($notification));

        return redirect()->back()->with('message', __('crud.created', ['type' => 'Notification']));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateNotification(Request $request, int $id)
    {
        if ($request->exists('delete')) {
            return $this->deleteNotification($request, $id);
        }

        if ($request->exists('restore')) {
            return $this->restoreNotification($request, $id);
        }

        $this->validate(
            $request,
            [
                'content'      => 'required|string|min:5',
                'level'        => 'required|int|between:0,3',
                'expired_at'   => 'required|date',
                'output'       => 'required|array|in:status,email,index',
                'order'        => 'required|int|between:0,5',
                'published_at' => 'required|date',
                'resend_mail'  => 'nullable',
            ]
        );

        try {
            $notification = Notification::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin_notifications_list')->withErrors(__('crud.not_found', ['type' => 'Notification']));
        }

        $data = $request->all();

        $data['expired_at'] = Carbon::parse($request->get('expired_at'));
        $data['published_at'] = Carbon::parse($request->get('published_at'));

        foreach (array_pull($data, 'output') as $type) {
            $data['output_'.$type] = true;
        }

        if (array_pull($data, 'resend_email', false) === 'resend_email') {
            event(new NotificationCreated($notification));
        }

        $notification->update($data);

        return redirect()->route('admin_notifications_list')->with('message', __('crud.updated', ['type' => 'Notification']));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteNotification(Request $request, int $id)
    {
        $type = 'message';
        $message = __('crud.deleted', ['type' => 'Notification']);

        try {
            Notification::findOrFail($id)->delete();
        } catch (ModelNotFoundException $e) {
            $type = 'errors';
            $message = __('crud.not_found', ['type' => 'Notification']);
        }

        return redirect()->route('admin_notifications_list')->with($type, $message);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreNotification(Request $request, int $id)
    {
        $type = 'message';
        $message = __('crud.restored', ['type' => 'Notification']);

        try {
            Notification::onlyTrashed()->findOrFail($id)->restore();
        } catch (ModelNotFoundException $e) {
            $type = 'errors';
            $message = __('crud.not_found', ['type' => 'Notification']);
        }

        return redirect()->route('admin_notifications_list')->with($type, $message);
    }
}
