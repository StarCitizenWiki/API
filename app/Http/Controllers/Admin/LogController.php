<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.09.2017
 * Time: 21:06
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jackiedo\LogReader\Facades\LogReader;

/**
 * Class LogController
 * @package App\Http\Controllers\Admin
 */
class LogController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLogsView(Request $request)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $logs = LogReader::level(['notice', 'warning', 'error', 'critical', 'danger', 'emergency'])->paginate(25);
        $logs->withPath('logs');

        return view('admin.logs')->with('logs', $logs);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markLogAsRead(Request $request)
    {
        if ($request->exists('mark_all')) {
            return $this->markAllAsRead();
        }

        $data = $this->validate(
            $request,
            [
                'mark_read' => 'present|array',
            ]
        );

        foreach ($data['mark_read'] as $id) {
            LogReader::find($id)->markAsRead();
        }

        return redirect()->route('admin.logs');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        LogReader::markAsRead();

        return redirect()->route('admin.logs');
    }
}
