<?php
class Rock_CoreOverride_Model_Cms_Resource_Page_Service extends Mage_Cms_Model_Resource_Page_Service
{
	 /**
     * Unlinks from $fromStoreId store pages that have same identifiers as pages in $byStoreId
     *
     * Routine is intended to be used before linking pages of some store ($byStoreId) to other store ($fromStoreId)
     * to prevent duplication of url keys
     *
     * Resolved $byLinkTable can be provided when restoring links from some backup table
     *
     * @param int $fromStoreId
     * @param int $byStoreId
     * @param string $byLinkTable
     *
     * @return Mage_Cms_Model_Mysql4_Page_Service
     */
    public function unlinkConflicts($fromStoreId, $byStoreId, $byLinkTable = null)
    {
        $readAdapter = $this->_getReadAdapter();

        $linkTable = $this->getTable('cms/page_store');
        $mainTable = $this->getMainTable();
        $byLinkTable = $byLinkTable ? $byLinkTable : $linkTable;

        // Select all page ids of $fromStoreId that have identifiers as some pages in $byStoreId
        $select = $readAdapter->select()
            ->from(array('from_link' => $linkTable), 'page_id')
            ->join(
                array('from_entity' => $mainTable),
                $readAdapter->quoteInto(
                    'from_entity.page_id = from_link.page_id AND from_link.store_id = ?',
                    $fromStoreId
                ),
                array()
            )->join(
                array('by_entity' => $mainTable),
                'from_entity.identifier = by_entity.identifier AND from_entity.page_id != by_entity.page_id',
                array()
            )->join(
                array('by_link' => $byLinkTable),
                $readAdapter->quoteInto('by_link.page_id = by_entity.page_id AND by_link.store_id = ?', $byStoreId),
                array()
            );

        $pageIds = $readAdapter->fetchCol($select);

        // Unlink found pages
        if ($pageIds) {
            $writeAdapter = $this->_getWriteAdapter();
            $where = array(
                'page_id IN (?)'   => $pageIds,
                'AND store_id = ?' => $fromStoreId
            );
            $writeAdapter->delete($linkTable, $where);
        }
        return $this;
    }
}
		