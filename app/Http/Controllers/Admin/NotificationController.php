<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Events\NotificationCreated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ProfilesMethodsTrait;
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
    use ProfilesMethodsTrait;

    /**
     * @return \Illuminate\View\View
     */
    public function showNotificationsListView(): View
    {
        return view('admin.notifications.index')->with(
            'notifications',
            Notification::withTrashed()->orderByDesc('created_at')->simplePaginate(10)
        );
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function showEditNotificationView(int $id): View
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__));
        try {
            $this->addTrace("Getting Notification with ID: {$id}", __FUNCTION__, __LINE__);
            $notification = Notification::withTrashed()->findOrFail($id);
            $this->stopProfiling(__FUNCTION__);

            return view('admin.notifications.edit')->with(
                'notification',
                $notification
            );
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("Notification with ID: {$id} not found");
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_notifications_list');
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
                'expires_at'   => 'required|date|after:'.Carbon::now(),
                'published_at' => 'required|date',
                'output'       => 'required|array|in:status,email,index',
            ]
        );

        $data = $request->all();

        $outputType = array_pull($data, 'output');

        foreach ($outputType as $type) {
            $data['output_'.$type] = true;
        }

        if (!is_null($data['expires_at'])) {
            $data['expires_at'] = Carbon::parse($data['expires_at'])->toDateTimeString();
        }

        $data['level'] = Notification::NOTIFICATION_LEVEL_TYPES[$data['level']];

        $notification = Notification::create($data);

        event(new NotificationCreated($notification));

        return redirect()->back();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteNotification(Request $request, int $id)
    {
        Notification::find($id)->delete();

        return redirect()->route('admin_notifications_list');
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
                'expires_at'   => 'required|date',
                'output'       => 'required|array|in:status,email,index',
                'order'        => 'required|int|between:0,5',
                'published_at' => 'required|date',
                'resend_mail'  => 'nullable',
            ]
        );

        try {
            $notification = Notification::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin_notifications_list')->withErrors('Not found');
        }

        $notification->content = $request->get('content');
        $notification->level = $request->get('level');
        $notification->expires_at = Carbon::parse($request->get('expires_at'));
        $notification->order = $request->get('order');
        $notification->published_at = Carbon::parse($request->get('published_at'));

        foreach ($request->get('output') as $type) {
            $notification->{'output_'.$type} = true;
        }

        $notification->save();

        if ($request->get('resend_email', false) === 'resend_email') {
            event(new NotificationCreated($notification));
        }

        return redirect()->route('admin_notifications_list');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreNotification(Request $request, int $id)
    {
        Notification::withTrashed()->find($id)->restore();

        return redirect()->route('admin_notifications_list');
    }
}
