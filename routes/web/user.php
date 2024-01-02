<?php declare(strict_types = 1);

use App\Models\Rsi\CommLink\Image\Image;

Route::get('/', static fn() => redirect()->to('/dashboard'))->name('index');

Route::get('/comm-links/{commLink}', 'Rsi\CommLink\CommLinkController@show')->name('comm-links.show');

Route::group(
    [],
    static function () {
        Route::namespace('Auth')
            ->name('auth.')
            ->group(
                static function () {
                    Route::group(
                        [],
                        static function () {
                            Route::get('/login', 'LoginController@showLoginForm')->name('login');
                            Route::get('/login/start', 'LoginController@redirectToProvider')->name('login.start');
                            Route::get('/login/callback', 'LoginController@handleProviderCallback')->name(
                                'login.callback'
                            );
                            Route::post('/logout', 'LoginController@logout')->name('logout');
                        }
                    );
                }
            );

        Route::get('dashboard', 'DashboardController@index')->name('dashboard');

        Route::name('dashboard.')
            ->prefix('dashboard')
            ->group(
                static function () {
                    Route::post(
                        'translate-comm-links',
                        'Job\Rsi\CommLink\JobController@startCommLinkTranslationJob'
                    )->name('translate-comm-links');

                    Route::post(
                        'download-comm-link-images',
                        'Job\Rsi\CommLink\JobController@startCommLinkImageDownloadJob'
                    )->name('download-comm-link-images');

                    Route::post(
                        'download-comm-links',
                        'Job\Rsi\CommLink\JobController@startCommLinkDownloadJob'
                    )->name('download-comm-links');


                    Route::post(
                        'create-wiki-pages',
                        'Job\Wiki\CommLink\JobController@startCommLinkWikiPageCreationJob'
                    )->name('create-wiki-pages');

                    Route::post(
                        'update-proofread-status',
                        'Job\Wiki\CommLink\JobController@startCommLinkProofReadStatusUpdateJob'
                    )->name('update-proofread-status');


                    Route::post(
                        'download-ship-matrix',
                        'Job\StarCitizen\Vehicle\JobController@startDownloadShipMatrixJob'
                    )->name('download-ship-matrix');
                    Route::post(
                        'import-vehicle-msrp',
                        'Job\StarCitizen\Vehicle\JobController@startMsrpImportJob'
                    )->name('import-vehicle-msrp');
                    Route::post(
                        'import-vehicle-loaner',
                        'Job\StarCitizen\Vehicle\JobController@startLoanerImportJob'
                    )->name('import-vehicle-loaner');


                    Route::post(
                        'import-galactapedia-categories',
                        'Job\StarCitizen\Galactapedia\JobController@startImportGalactapediaCategoriesJob'
                    )->name('import-galactapedia-categories');
                    Route::post(
                        'import-galactapedia-articles',
                        'Job\StarCitizen\Galactapedia\JobController@startImportGalactapediaArticlesJob'
                    )->name('import-galactapedia-articles');
                    Route::post(
                        'import-galactapedia-article-properties',
                        'Job\StarCitizen\Galactapedia\JobController@startImportGalactapediaArticlePropertiesJob'
                    )->name('import-galactapedia-article-properties');
                    Route::post(
                        'create-galactapedia-pages',
                        'Job\StarCitizen\Galactapedia\JobController@startCreateWikiPagesJob'
                    )->name('create-galactapedia-pages');


                    Route::post(
                        'import-sc-items',
                        'Job\SC\JobController@startItemImportJob'
                    )->name('import-sc-items');
                    Route::post(
                        'import-sc-vehicles',
                        'Job\SC\JobController@startVehicleImportJob'
                    )->name('import-sc-vehicles');
                    Route::post(
                        'import-sc-shops',
                        'Job\SC\JobController@startShopImportJob'
                    )->name('import-sc-shops');
                    Route::post(
                        'upload-sc-images',
                        'Job\SC\JobController@startImageUploadJob'
                    )->name('upload-sc-images');
                    Route::post(
                        'translate-sc-images',
                        'Job\SC\JobController@startTranslateJob'
                    )->name('translate-sc-items');
                }
            );

        Route::namespace('Account')
            ->name('account.')
            ->group(
                static function () {
                    Route::get('account', 'AccountController@index')->name('index');
                    Route::patch('account', 'AccountController@update')->name('update');
                }
            );

        Route::namespace('Job')
            ->name('jobs.')
            ->prefix('jobs')
            ->group(
                static function () {
                    Route::get('failed', 'JobController@viewFailed')->name('failed');
                    Route::post('truncate', 'JobController@truncate')->name('truncate');
                }
            );

        Route::resources(
            [
                'users' => 'User\UserController',
                'changelogs' => 'Changelog\ChangelogController',
                'transcripts' => 'Transcript\TranscriptController',
            ]
        );

        Route::namespace('StarCitizen')
            ->name('starcitizen.')
            ->prefix('starcitizen')
            ->group(
                static function () {
                    Route::resources(
                        [
                            'manufacturers' => 'Manufacturer\ManufacturerController',
                            'production-statuses' => 'ProductionStatus\ProductionStatusController',
                            'production-notes' => 'ProductionNote\ProductionNoteController',
                            'galactapedia' => 'Galactapedia\GalactapediaController',
                        ]
                    );

                    Route::prefix('starmap')
                        ->name('starmap.')
                        ->namespace('Starmap')
                        ->group(
                            static function () {
                                Route::resources(
                                    [
                                        'starsystems' => 'Starsystem\StarsystemController',
                                        'celestial_objects' => 'CelestialObject\CelestialObjectController',
                                        'jumppoint' => 'Jumppoint\JumppointController',
                                    ]
                                );
                            }
                        );

                    Route::prefix('vehicles')
                        ->name('vehicles.')
                        ->namespace('Vehicle')
                        ->group(
                            static function () {
                                Route::resources(
                                    [
                                        'ships' => 'Ship\ShipController',
                                        'ground-vehicles' => 'GroundVehicle\GroundVehicleController',
                                        'sizes' => 'Size\SizeController',
                                        'foci' => 'Focus\FocusController',
                                        'types' => 'Type\TypeController',
                                    ]
                                );
                            }
                        );
                }
            );

        Route::namespace('Rsi')
            ->name('rsi.')
            ->prefix('rsi')
            ->group(
                static function () {
                    Route::namespace('CommLink')
                        ->name('comm-links.')
                        ->prefix('comm-links')
                        ->group(
                            static function () {
                                Route::get('categories', 'Category\CategoryController@index')->name('categories.index');
                                Route::get('categories/{category}', 'Category\CategoryController@show')->name('categories.show');

                                Route::get('channels', 'Channel\ChannelController@index')->name('channels.index');
                                Route::get('channels/{channel}', 'Channel\ChannelController@show')->name('channels.show');

                                Route::get('series', 'Series\SeriesController@index')->name('series.index');
                                Route::get('series/{series}', 'Series\SeriesController@show')->name('series.show');

                                Route::get('images', 'Image\ImageController@index')->name('images.index');
                                Route::post('images/upload-wiki', 'Image\ImageController@upload')->name('images.upload-wiki');
                                Route::post('images/search', 'Image\ImageController@search')->name('images.search');
                                Route::get('images/{image}/similar', 'Image\ImageController@similarImages')->name('images.similar');
                                Route::get('images/{image}/tags', 'Image\ImageController@editTags')->name('images.edit-tags');
                                Route::patch('images/{image}/tags', 'Image\ImageController@saveTags')->name('images.save-tags');
                                Route::get('images/start-edit', static function () {
                                    $image = Image::query()->inRandomOrder()->limit(1)->firstOrFail();

                                    return redirect(route('web.rsi.comm-links.images.edit-tags', $image->getRouteKey()));
                                })->name('images.start-edit');
                                Route::get('images/tag-{tag}', 'Image\ImageController@indexByTag')->name('images.index-by-tag');
                                Route::get('images/{image}', 'Image\ImageController@show')->name('images.show');

                                Route::get('image-tags', 'Image\TagController@index')->name('image-tags.index');
                                Route::post('image-tags', 'Image\TagController@post')->name('image-tags.create');
                                Route::get('image-tags/{tag}', 'Image\TagController@edit')->name('image-tags.edit');
                                Route::post('image-tags/{tag}', 'Image\TagController@update')->name('image-tags.update');

                                Route::get('search', 'CommLinkSearchController@search')->name('search');
                                Route::post('reverse-image-link-search', 'CommLinkSearchController@reverseImageLinkSearchPost')->name('reverse-image-link-search.post');
                                Route::post('reverse-image-search', 'CommLinkSearchController@reverseImageSearchPost')->name('reverse-image-search.post');

                                Route::post('text-search', 'CommLinkSearchController@textSearchPost')->name('text-search.post');
                                Route::post('search', 'CommLinkSearchController@searchByTitle')->name('search-by-title.post');
                            }
                        );

                    Route::resources(
                        [
                            'comm-links' => 'CommLink\CommLinkController',
                        ]
                    );

                    Route::get(
                        'comm-links/{comm_link}/{version}/preview',
                        'CommLink\CommLinkController@preview'
                    )->name('comm-links.preview');

                    Route::namespace('Stat')
                        ->name('stat.')

                        ->group(
                            static function () {
                                Route::get('stats', 'StatController@index')->name('index');
                            }
                        );
                }
            );


        Route::namespace('StarCitizenUnpacked')
            ->name('starcitizenunpacked.')
            ->group(
                static function () {
                    Route::resources(
                        [
                            'items' => 'Item\ItemController',
                            'vehicles' => 'VehicleController',
                        ]
                    );
                }
            );
    }
);
