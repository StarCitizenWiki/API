<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTranslationRequest;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\System\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

class TranslationController extends Controller
{
    public function index(): View
    {
        $commLinks = CommLink::query()
            ->select('id', 'cig_id', 'title', 'translation', 'created_at')
            ->orderBy('cig_id', 'desc')
            ->paginate(25, ['*'], 'commlinks_page');

        $articles = Article::query()
            ->select('id', 'cig_id', 'title', 'translation', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'articles_page');

        $smSizes = Size::query()->get();
        $smFocuses = Focus::query()->get();
        $smTypes = Type::query()->get();

        return view('admin.translations.index', compact('commLinks', 'articles', 'smSizes', 'smFocuses', 'smTypes'));
    }

    public function edit(string $type, int $id): View
    {
        $model = $this->resolveModel($type, $id);

        $translations = [
            Language::ENGLISH => $model->getTranslation('translation', Language::ENGLISH, false) ?? '',
            Language::GERMAN => $model->getTranslation('translation', Language::GERMAN, false) ?? '',
            Language::CHINESE => $model->getTranslation('translation', Language::CHINESE, false) ?? '',
            Language::FRENCH => $model->getTranslation('translation', Language::FRENCH, false) ?? '',
        ];

        return view('admin.translations.edit', compact('model', 'type', 'translations'));
    }

    public function update(UpdateTranslationRequest $request, string $type, int $id): RedirectResponse
    {
        $model = $this->resolveModel($type, $id);

        $translationsData = array_filter($request->input('translations'), function ($translation) {
            return ! empty($translation);
        });

        $model->setTranslations('translation', $translationsData);
        $model->save();

        return redirect()->route('admin.translations.index')
            ->with('success', 'Translations updated successfully.');
    }

    private function resolveModel(string $type, int $id): Model
    {
        return match ($type) {
            'comm-link' => CommLink::findOrFail($id),
            'article' => Article::findOrFail($id),
            'smSize' => Size::findOrFail($id),
            'smFocus' => Focus::findOrFail($id),
            'smType' => Type::findOrFail($id),
            default => abort(404),
        };
    }
}
