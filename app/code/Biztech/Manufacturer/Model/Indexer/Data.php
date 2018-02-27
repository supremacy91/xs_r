<?php

namespace Biztech\Manufacturer\Model\Indexer;
use Biztech\Manufacturer\Model\Indexer\Data\Action\Row;
use Biztech\Manufacturer\Model\Indexer\Data\Action\Rows;
use Biztech\Manufacturer\Model\Indexer\Data\Action\Full;
use Magento\Framework\Indexer\ActionInterface;
class Data implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
	/**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row
     */
    protected $manufacturerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows
     */
    protected $manufacturerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full
     */
    protected $manufacturersFull;

    public function __construct(
        Row $manufacturerRow,
        Rows $manufacturerRows,
        Full $manufacturersFull
    ) {
        $this->manufacturerRow = $manufacturerRow;
        $this->manufacturerRows = $manufacturerRows;
        $this->manufacturersFull = $manufacturersFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->manufacturerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->manufacturersFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->manufacturerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->manufacturerRow->execute($id);
    }
}