<?php

namespace Biztech\Manufacturer\Model\Indexer\Url\Plugin;

use Biztech\Manufacturer\Model\Indexer\Url\Processor;
use Magento\Indexer\Model\Indexer\State;

class IndexerState
{
    /**
     * @var State
     */
    protected $state;

    /**
     * Related indexers IDs
     *
     * @var int[]
     */
    protected $indexerIds = [
        Processor::INDEXER_ID
    ];

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Synchronize status for indexers
     *
     * @param State $state
     * @return State
     */
    public function afterSave(State $state)
    {
        if (in_array($state->getIndexerId(), $this->indexerIds)) {
            $indexerId = $state->getIndexerId() === Processor::INDEXER_ID
                ? Processor::INDEXER_ID
                : null;

            $relatedIndexerState = $this->state->loadByIndexer($indexerId);

            if ($relatedIndexerState->getStatus() !== $state->getStatus()) {
                $relatedIndexerState->setData('status', $state->getStatus());
                $relatedIndexerState->save();
            }
        }

        return $state;
    }
}
