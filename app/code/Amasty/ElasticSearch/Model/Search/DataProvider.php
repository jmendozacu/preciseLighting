<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;

class DataProvider implements \Magento\Framework\Search\Dynamic\DataProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Range
     */
    private $range;

    /**
     * @var \Magento\Framework\Search\Dynamic\IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var string
     */
    private $indexerId;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetRequestQuery\GetAggregations\FieldMapper
     */
    private $fieldMapper;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        \Magento\Catalog\Model\Layer\Filter\Price\Range $range,
        \Magento\Framework\Search\Dynamic\IntervalFactory $intervalFactory,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Amasty\ElasticSearch\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations\FieldMapper $fieldMapper,
        $indexerId = \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID
    ) {
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->scopeResolver = $scopeResolver;
        $this->clientRepository = $clientRepository;
        $this->indexerId = $indexerId;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @return array|int
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregatorParams = ['count', 'max', 'min', 'std'];

        $query = $this->generateAggregationsQuery($entityStorage);
        $queryResult = $this->clientRepository->get()->search($query);

        $result = [];
        foreach ($aggregatorParams as $aggregator) {
            $result[$aggregator] = isset($queryResult['aggregations']['prices'][$aggregator]) ?
                $queryResult['aggregations']['prices'][$aggregator] : 0;
        }

        return $result;
    }

    /**
     * @param EntityStorage $entityStorage
     * @return array
     */
    private function generateAggregationsQuery(EntityStorage $entityStorage)
    {
        $query = $this->generateGeneralQuery($entityStorage);

        $query['body']['aggregations'] = [
            'prices' => [
                'extended_stats' => [
                    'field' => $this->fieldMapper->mapFieldName('price'),
                ],
            ],
        ];

        return $query;
    }

    /**
     * @param EntityStorage $entityStorage
     * @param $fieldName
     * @param $range
     * @param array $dimensions
     * @return array
     */
    private function generateAggregationQuery(EntityStorage $entityStorage, $fieldName, $range, $dimensions = [])
    {
        $query = $this->generateGeneralQuery($entityStorage, $dimensions);

        $query['body']['aggregations'] = [
            'prices' => [
                'histogram' => [
                    'field' => $fieldName,
                    'interval' => $range,
                ],
            ],
        ];

        return $query;
    }

    /**
     * @param EntityStorage $entityStorage
     * @param array $dimensions
     * @return array
     */
    private function generateGeneralQuery(EntityStorage $entityStorage, array $dimensions = [])
    {
        $entityIds = $entityStorage->getSource();
        $dimension = current($dimensions);
        $storeId = false !== $dimension
            ? $this->scopeResolver->getScope($dimension->getValue())->getId()
            : $this->storeManager->getStore()->getId();

        $query = [
            'index' => $this->clientRepository->get()->getIndexName($this->indexerId, $storeId),
            'type' => $this->config->getEntityType(),
            'body' => [
                'stored_fields' => [
                    '_id',
                    '_score',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    '_id' => $entityIds,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $query;
    }

    /**
     * @param \Magento\Framework\Search\Request\BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return \Magento\Framework\Search\Dynamic\IntervalInterface
     */
    public function getInterval(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        return $this->intervalFactory->create(
            [
                'entityIds' => $entityStorage->getSource(),
                'storeId' => $storeId,
                'fieldName' => 'price',
            ]
        );
    }

    /**
     * @param \Magento\Framework\Search\Request\BucketInterface $bucket
     * @param array $dimensions
     * @param int $range
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getAggregation(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $result = [];

        $fieldName = $this->fieldMapper->mapFieldName($bucket->getField());
        $query = $this->generateAggregationQuery($entityStorage, $fieldName, $range, $dimensions);

        $queryResult = $this->clientRepository->get()->search($query);
        foreach ($queryResult['aggregations']['prices']['buckets'] as $bucket) {
            $key = (int)($bucket['key'] / $range + 1);
            $result[$key] = $bucket['doc_count'];
        }

        return $result;
    }

    /**
     * @param int $range
     * @param array $ranges
     * @return array
     */
    public function prepareData($range, array $ranges)
    {
        $result = [];
        if (!empty($ranges)) {
            $lastIndex = array_keys($ranges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];
            foreach ($ranges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;

                $result[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $result;
    }
}
