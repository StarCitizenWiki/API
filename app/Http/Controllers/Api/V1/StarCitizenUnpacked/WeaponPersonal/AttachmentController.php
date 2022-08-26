<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachmentsTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends ApiController
{
    /**
     * @param WeaponPersonalAttachmentsTransformer $transformer
     * @param Request $request
     */
    public function __construct(WeaponPersonalAttachmentsTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Attachment::query()
            ->where('version', config(self::SC_DATA_KEY)));
    }

    public function show(Request $request): Response
    {
        ['attachment' => $attachment] = Validator::validate(
            [
                'attachment' => $request->attachment,
            ],
            [
                'attachment' => 'required|string|min:1|max:255',
            ]
        );

        $attachment = $this->cleanQueryName($attachment);

        try {
            $attachment = Attachment::query()
                ->whereHas('item', function (Builder $query) use ($attachment) {
                    return $query->where('name', 'LIKE', $attachment . '%')
                        ->orWhere('uuid', $attachment);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $attachment));
        }

        return $this->getResponse($attachment);
    }
}
