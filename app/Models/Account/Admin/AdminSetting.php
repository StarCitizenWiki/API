<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminSetting
 */
class AdminSetting extends Model
{
    protected $fillable = [
        'editor_license_accepted',
    ];

    protected $casts = [
        'editor_license_accepted' => 'boolean',
    ];

    /**
     * The associated Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
