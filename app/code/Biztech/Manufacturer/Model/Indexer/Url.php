<?php

namespace Biztech\Manufacturer\Model\Indexer;

use Biztech\Manufacturer\Model\Indexer\Url\Action\Row;
use Biztech\Manufacturer\Model\Indexer\Url\Action\Rows;
use Biztech\Manufacturer\Model\Indexer\Url\Action\Full;
use Magento\Framework\Indexer\ActionInterface;

class Url implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row
     */
    protected $manufacturerUrl;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows
     */
    protected $manufacturerUrls;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full
     */
    protected $manufacturerUrlsFull;


    public function __construct(
        Row $manufacturerUrl,
        Rows $manufacturerUrls,
        Full $manufacturerUrlsFull
    )
    {
        $this->manufacturerUrl = $manufacturerUrl;
        $this->manufacturerUrls = $manufacturerUrls;
        $this->manufacturerUrlsFull = $manufacturerUrlsFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->manufacturerUrls->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->manufacturerUrlsFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->manufacturerUrls->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->manufacturerUrl->execute($id);
    }
}