<?php
namespace Gurucomkz\EagerLoading;

use Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Replaces DataList when EagerLoading is used. Fetches data when the main query is actually executed.
 * Appends related objects when a DataObject is actually created.
 */
class EagerLoadedDataList extends DataList
{

    const ID_LIMIT = 5000;
    public $withList = [];
    public $withListOriginal = [];
    public $eagerLoadingRelatedMaps = [
        'has_one' => [],
        'has_many' => [],
        'many_many' => [],
    ];

    public function __construct($classOrList)
    {
        if (is_string($classOrList)) {
            parent::__construct($classOrList);
        } else {
            parent::__construct($classOrList->dataClass());
            $this->dataQuery = $classOrList->dataQuery();
        }
    }

    public $eagerLoadingRelatedCache = [];
    public static function cloneFrom(DataList $list)
    {
        $clone = new EagerLoadedDataList($list);

        $clone->withList = $list->withList;
        $clone->withListOriginal = $list->withListOriginal;
        $clone->eagerLoadingRelatedCache = $list->eagerLoadingRelatedCache;
        return $clone;
    }

    /**
     * Create a DataObject from the given SQL row
     *
     * @param array $row
     * @return DataObject
     */
    public function createDataObject($row)
    {
        $this->prepareEagerRelations();
        $item = parent::createDataObject($row);

        $this->fulfillEagerRelations($item);
        return $item;
    }

    private $relationsPrepared = false;

    private function filterWithList($list)
    {
        return array_filter(
            $this->withList,
            function ($dep) use ($list) {
                return array_key_exists($dep[0], $list);
            }
        );
    }

    public function prepareEagerRelations()
    {
        if ($this->relationsPrepared) {
            return;
        }
        $this->relationsPrepared = true;
        $localClass = $this->dataClass();
        $config = Config::forClass($localClass);
        $hasOnes = (array) $config->get('has_one');
        $hasManys = (array) $config->get('has_many');
        $manyManys = (array) $config->get('many_many');
        $belongsManyManys = (array) $config->get('belongs_many_many');

        //collect has_ones
        $withHasOnes = $this->filterWithList($hasOnes);
        $withHasManys = $this->filterWithList($hasManys);
        $withManyManys = $this->filterWithList($manyManys);
        $withBelongsManyManys = $this->filterWithList($belongsManyManys);

        if (!count($withHasOnes) && !count($withHasManys) && !count($withManyManys) && !count($withBelongsManyManys)) {
            // Injector::inst()->get(LoggerInterface::class)
            // ->debug("Invalid names supplied for ->with(" . implode(', ', $this->withListOriginal) . ")");
            return;
        }

        $data = $this->column('ID');
        if (count($withHasOnes)) {
            $this->eagerLoadingPrepareCache($hasOnes, $withHasOnes);
            $this->eagerLoadHasOne($data, $hasOnes, $withHasOnes);
        }
        if (count($withHasManys)) {
            $this->eagerLoadingPrepareCache($hasManys, $withHasManys);
            $this->eagerLoadHasMany($data, $hasManys, $withHasManys);
        }
        if (count($withManyManys)) {
            $this->eagerLoadingPrepareCache($manyManys, $withManyManys);
            $this->eagerLoadManyMany($data, $manyManys, $withManyManys);
        }
        if (count($withBelongsManyManys)) {
            $this->eagerLoadingPrepareCache($belongsManyManys, $withBelongsManyManys);
            $this->eagerLoadManyMany($data, $belongsManyManys, $withBelongsManyManys);
        }
    }

    public function eagerLoadHasOne(&$ids, $hasOnes, $withHasOnes)
    {
        //collect required IDS
        $fields = ['ID'];
        foreach ($withHasOnes as $depSeq) {
            $dep = $depSeq[0];
            $fields[] = "\"{$dep}ID\"";
        }
        $table = DataObject::getSchema()->tableName($this->dataClass);
        $data = new SQLSelect($fields, '"' . $table . '"', ['"ID" IN (' . implode(',', $ids) . ')']);
        $data = Utils::EnsureArray($data->execute(), 'ID');

        foreach ($withHasOnes as $depSeq) {
            $dep = $depSeq[0];
            $depClass = $hasOnes[$dep];

            $descriptor = [
                'class' => $depClass,
                'localField' => "{$dep}ID",
                'map' => [],
            ];

            $descriptor['map'] = Utils::extractField($data, $descriptor['localField']);
            $uniqueIDs = array_unique($descriptor['map']);
            while (count($uniqueIDs)) {
                $IDsubset = array_splice($uniqueIDs, 0, self::ID_LIMIT);
                $result = DataObject::get($depClass)->filter('ID', $IDsubset);
                if (count($depSeq)>1) {
                    $result = $result
                        ->with(implode('.', array_slice($depSeq, 1)));
                }

                foreach ($result as $depRecord) {
                    $this->eagerLoadingRelatedCache[$depClass][$depRecord->ID] = $depRecord;
                }
            }

            $this->eagerLoadingRelatedMaps['has_one'][$dep] = $descriptor;
        }
    }

    public function eagerLoadHasMany($data, $hasManys, $withHasManys)
    {
        $localClass = $this->dataClass();
        $localClassTail = basename(str_replace('\\', '/', $localClass));

        foreach ($withHasManys as $depSeq) {
            $dep = $depSeq[0];
            $depClass = $hasManys[$dep];
            if (false !== strpos($depClass, '.')) {
                $dcSplit = explode('.', $depClass, 2);
                $depClass = $dcSplit[0];
                $localNameInDep = $dcSplit[1];
            } else {
                $localNameInDep = $localClassTail;
            }
            $depKey = "{$localNameInDep}ID";
            $descriptor = [
                'class' => $depClass,
                'remoteRelation' => $localNameInDep,
                'remoteField' => $depKey,
                'map' => [],
            ];
            $result = DataObject::get($depClass)->filter($depKey, $data);
            if (count($depSeq)>1) {
                $result = $result
                    ->with(implode('.', array_slice($depSeq, 1)));
            }

            $collection = [];

            foreach ($data as $localRecordID) {
                $collection[$localRecordID] = [];
            }
            foreach ($result as $depRecord) {
                $this->eagerLoadingRelatedCache[$depClass][$depRecord->ID] = $depRecord;
                $collection[$depRecord->$depKey][] = $depRecord->ID;
            }
            $descriptor['map'] = $collection;
            $this->eagerLoadingRelatedMaps['has_many'][$dep] = $descriptor;
        }
    }

    public function eagerLoadManyMany(&$data, $manyManys, $withManyManys)
    {
        $localClass = $this->dataClass();
        $schema = DataObject::getSchema();

        foreach ($withManyManys as $depSeq) {
            $dep = $depSeq[0];
            $depData = $manyManys[$dep];
            $sort = [];

            if (is_array($depData)) {
                if (!isset($depData['from']) || !isset($depData['to']) || !isset($depData['through'])) {
                    throw new Exception(sprintf('Incompatible "many_many through" configuration for %s.%s', $localClass, $dep));
                }
                $throughClass = $depData['through'];
                // determine the target data object
                $depClass = $schema->hasOneComponent($depData['through'], $depData['to']);
                if (!$depClass) {
                    throw new Exception(sprintf('Class %s does not have a $has_one component named', $depData['through'], $depData['to']));
                }

                $table = DataObject::getSchema()->tableName($throughClass);

                $childField = $depData['to']. 'ID';
                $parentField = $depData['from']. 'ID';

                if ($defaultSort = Config::inst()->get($throughClass, 'default_sort')) {
                    $sort[] = $defaultSort;
                }
            } else {
                $depClass = $depData;
                $component = $schema->manyManyComponent($localClass, $dep);

                $table = $component['join'];
                $childField = $component['childField'];
                $parentField = $component['parentField'];
            }

            $descriptor = [
                'class' => $depClass,
                'map' => [],
            ];

            $idsQuery = SQLSelect::create(
                [
                    '"' . $childField . '"',
                    '"' . $parentField . '"',
                ],
                '"' . $table . '"',
                [
                    '"' . $parentField . '" IN (' . implode(',', $data) . ')'
                ],
                $sort,
            )->execute();

            $collection = [];
            $relListReverted = [];
            foreach ($idsQuery as $row) {
                $relID = $row[$childField];
                $localID = $row[$parentField];
                if (!isset($collection[$localID])) {
                    $collection[$localID] = [];
                }
                $collection[$localID][] = $relID;
                $relListReverted[$relID] = 1; //use ids as keys to avoid
            }

            if (count($relListReverted)) {
                $result = DataObject::get($depClass)->filter('ID', array_keys($relListReverted));
                if (count($depSeq)>1) {
                    $result = $result
                        ->with(implode('.', array_slice($depSeq, 1)));
                }

                foreach ($result as $depRecord) {
                    $this->eagerLoadingRelatedCache[$depClass][$depRecord->ID] = $depRecord;
                }
            }

            $descriptor['map'] = $collection;
            $this->eagerLoadingRelatedMaps['many_many'][$dep] = $descriptor;
        }
    }

    public function fulfillEagerRelations(DataObject $item)
    {
        foreach ($this->eagerLoadingRelatedMaps['has_one'] as $dep => $depInfo) {
            $depClass = $depInfo['class'];
            if (isset($depInfo['map'][$item->ID])) {
                $depID = $depInfo['map'][$item->ID];
                if (isset($this->eagerLoadingRelatedCache[$depClass][$depID])) {
                    $depRecord = $this->eagerLoadingRelatedCache[$depClass][$depID];
                    $item->setComponent($dep, $depRecord);
                }
            }
        }

        foreach ($this->eagerLoadingRelatedMaps['has_many'] as $dep => $depInfo) {
            $depClass = $depInfo['class'];
            $collection = [];
            if (isset($depInfo['map'][$item->ID])) {
                foreach ($depInfo['map'][$item->ID] as $depID) {
                    if (isset($this->eagerLoadingRelatedCache[$depClass][$depID])) {
                        $depRecord = $this->eagerLoadingRelatedCache[$depClass][$depID];
                        $collection[] = $depRecord;
                    }
                }
            }
            if (!method_exists($item, 'addEagerRelation')) {
                throw new EagerLoadingException(
                    "Model {$item->ClassName} must include " .
                    EagerLoaderMultiAccessor::class .
                    " trait to use eager loading for \$has_many"
                );
            }
            $item->addEagerRelation($dep, $collection);
        }

        foreach ($this->eagerLoadingRelatedMaps['many_many'] as $dep => $depInfo) {
            $depClass = $depInfo['class'];
            $collection = [];
            if (isset($depInfo['map'][$item->ID])) {
                foreach ($depInfo['map'][$item->ID] as $depID) {
                    // foreach ($depIDlist as $depID) {
                        if (isset($this->eagerLoadingRelatedCache[$depClass][$depID])) {
                            $depRecord = $this->eagerLoadingRelatedCache[$depClass][$depID];
                            $collection[] = $depRecord;
                        }
                    // }
                }
            }
            if (!method_exists($item, 'addEagerRelation')) {
                throw new EagerLoadingException(
                    "Model {$item->ClassName} must include " .
                    EagerLoaderMultiAccessor::class .
                    " trait to use eager loading for \$many_many"
                );
            }
            $item->addEagerRelation($dep, $collection);
        }
    }
    /**
     * Returns a generator for this DataList
     *
     * @return \Generator&DataObject[]
     */
    public function getGenerator()
    {
        $query = $this->dataQuery()->execute();

        while ($row = $query->record()) {
            yield $this->createDataObject($row);
        }
    }

    private function eagerLoadingPrepareCache($all, $selected)
    {
        foreach ($selected as $depSeq) {
            $dep = $depSeq[0];
            $depClass = $all[$dep];
            $depClass = $depClass['through'] ?? $depClass;
            if (!isset($this->eagerLoadingRelatedCache[$depClass])) {
                $this->eagerLoadingRelatedCache[$depClass] = [];
            }
        }
    }
}
