<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CommLinkImageSearchService
{
    public function __construct(private readonly PdqHasher $hasher) {}

    public function resolveByMediaUrl(string $url): ?Image
    {
        $path = parse_url(ImageParser::cleanImgSource($url), PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $dir = ImageParser::getDirHash($path);
        $query = Image::query();

        if ($dir === 'i') {
            $parts = explode('/', $path);
            array_pop($parts);
            $prefix = implode('/', $parts);

            return $query->where('src', 'LIKE', $prefix.'%')->first();
        }

        return $query->where('dir', $dir)->first();
    }

    /**
     * @param  UploadedFile|string  $image  Uploaded image file or raw image contents
     * @return Collection<int, Image>
     */
    public function similarImages(UploadedFile|string $image, int $similarity): Collection
    {
        $this->checkExtensionsLoaded();

        try {
            $hashResult = $this->hasher->hashContents($this->imageContents($image));
        } catch (RuntimeException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }

        return ImageHash::similarImagesForHash($hashResult->toBitString(), $similarity);
    }

    /**
     * Reads the upload before the GD check so the Web side keeps answering
     * unreadable uploads with 422 ahead of the 501 availability error.
     *
     * @return Collection<int, Image>
     */
    public function similarImagesFromUpload(UploadedFile $image, int $similarity): Collection
    {
        return $this->similarImages($this->imageContents($image), $similarity);
    }

    private function imageContents(UploadedFile|string $image): string
    {
        if (is_string($image)) {
            return $image;
        }

        $contents = file_get_contents($image->getPathname());

        if ($contents === false) {
            throw new HttpException(422, 'Unable to read uploaded image.');
        }

        return $contents;
    }

    /**
     * @throws HttpException
     */
    private function checkExtensionsLoaded(): void
    {
        if (! extension_loaded('gd')) {
            Log::error('Required extension "GD" not available.');

            throw new HttpException(501, 'Required extension "GD" not available.');
        }
    }
}
