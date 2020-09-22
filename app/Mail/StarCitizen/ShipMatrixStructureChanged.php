<?php

declare(strict_types=1);

namespace App\Mail\StarCitizen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShipMatrixStructureChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->markdown('emails.starcitizen.shipmatrix.structure_changed');
    }
}
