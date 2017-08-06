<?php declare(strict_types = 1);

namespace App\Transformers\StarCitizenWiki;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class SMWTransformer
 * @package App\Transformers\StarCitizenWiki
 */
class SMWTransformer extends AbstractBaseTransformer
{
    private const SMW_KEYS = [
        '_DTITLE' => 'page_title',
        '_INST'   => 'categories',
        '_MDAT'   => 'last_modified',
        '_SOBJ'   => 'sub_objects',
        '_SKEY'   => 'search_key',
    ];

    /**
     * Transformes the given data
     *
     * @param mixed $data Data to transform
     *
     * @return mixed
     */
    public function transform($data)
    {
        array_walk_recursive(
            $data,
            function (&$value) {
                $value = preg_replace('/#[0-9]{1,3}#/', '', $value);
            }
        );

        $title = str_replace(' ', '_', $data['query']['subject']);
        $transformed = [];

        foreach ($data['query']['data'] as $shipData) {
            if (!starts_with($shipData['property'], '_')) {
                $items = [];
                foreach ($shipData['dataitem'] as $item) {
                    $items[] = $item['item'];
                }
                $transformed += [
                    $shipData['property'] => $items,
                ];
            } else {
                $key = self::SMW_KEYS[$shipData['property']] ?? $shipData['property'];
                $transformed['smw'][$key] = array_flatten($shipData['dataitem']);
                $transformed['smw'][$key] = array_filter(
                    $transformed['smw'][$key],
                    function ($value) {
                        return !is_int($value);
                    }
                );
                sort($transformed['smw'][$key], SORT_NUMERIC);
            }

            if ('_DTITLE' === $shipData['property']) {
                $title = $shipData['dataitem'][0]['item'];
            }
        }

        foreach ($data['query']['sobj'] ?? [] as $shipData) {
            $objectData = [];
            $subjectTitle = last(explode('#', $shipData['subject']));
            foreach ($shipData['data'] as $item) {
                if (!starts_with($item['property'], '_')) {
                    $objectData[] = [
                        $item['property'] => $item['dataitem'][0]['item'],
                    ];
                }
            }
            $transformed += [
                $subjectTitle => $objectData,
            ];
        }

        $transformed = [
            'subject' => $data['query']['subject'],
            $title    => $transformed,
        ];

        return $transformed;
    }
}
