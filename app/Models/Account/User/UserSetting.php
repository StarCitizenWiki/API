<?php declare(strict_types = 1);

namespace App\Models\Account\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminSetting
 */
class UserSetting extends Model
{
    protected $fillable = [
        'editor_license_accepted',
        'editor_receive_emails',
    ];

    protected $casts = [
        'editor_license_accepted' => 'boolean',
        'editor_receive_emails' => 'boolean',
    ];

    /**
     * The associated Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function editorLicenseAccepted(): bool
    {
        return $this->editor_license_accepted ?? false;
    }

    /**
     * @return bool
     */
    public function editorReceiveEmails(): bool
    {
        return $this->editor_receive_emails ?? false;
    }

    /**
     * @return bool
     */
    public function receiveApiNotifications(): bool
    {
        return $this->receive_api_notifications ?? false;
    }
}
