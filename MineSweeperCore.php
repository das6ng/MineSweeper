<?php
/**
 * Created by PhpStorm.
 * User: Dash
 * Date: 2019/3/11
 * Time: 18:24
 */

namespace Dash;


class Cell {
    private $hasMine;
    private $isVisible;
    private $hasShovel;
    private $neighbors;

    /**
     * Cell constructor.
     */
    public function __construct() {
        $this->hasMine = false;
        $this->hasShovel = false;
        $this->isVisible = false;
        $this->neighbors = 0;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool {
        return $this->isVisible;
    }

    public function setVisible(): void {
        $this->isVisible = true;
    }

    /**
     * @return bool
     */
    public function hasShovel(): bool {
        return $this->hasShovel;
    }

    public function putShovel(): void {
        $this->hasShovel = true;
    }

    public function removeShovel():void {
        $this->hasShovel = false;
    }

    /**
     * @return bool
     */
    public function hasMine(): bool {
        return $this->hasMine;
    }

    /**
     */
    public function setMine(): void {
        $this->hasMine = true;
    }

    /**
     * @return int
     */
    public function getNeighbors(): int {
        return $this->neighbors;
    }

    /**
     * @param int $neighbors
     */
    public function setNeighbors(int $neighbors): void {
        $this->neighbors = $neighbors;
    }

}

class MineSweeper {
    private $grid;
    private $rows;
    private $columns;

    private $processedNum;
    private $shovels;

    /**
     * MineSweeper constructor.
     * @param $rows
     * @param $columns
     */
    public function __construct($rows, $columns) {
        $this->rows = $rows;
        $this->columns = $columns;
        for ($i = 0; $i < $rows * $columns; $i++) {
            $this->grid[$i] = new Cell();
        }
        $this->processedNum = 0;
        $this->shovels = 0;
    }

    /**
     * @param $mineNum
     * @return bool
     */
    public function init($mineNum) {
        if ($mineNum > $this->rows * $this->columns) {
            return false;
        }
        for ($i = 0; $i < $mineNum; $i++) {
            if (isset($this->grid[$i]) && !empty($this->grid[$i])) {
                /** @var Cell $tmpObj */
                $tmpObj = $this->grid[$i];
                $tmpObj->setMine();
            }
        }
        shuffle($this->grid);
        $this->_calcNeighbors();
        $this->shovels = $mineNum;

        return true;
    }

    /**
     * @param $grid : static int array , 1 indicates has mine
     */
    public function initStatic($grid) {
        if (count($grid) != $this->rows * $this->columns) {
            return;
        }
        for ($i = 0; $i < 100; $i++) {
            if ($grid[$i] == 1) {
                /** @var Cell $tmpObj */
                $tmpObj = &$this->grid[$i];
                $tmpObj->setMine();
            }
        }
        $this->_calcNeighbors();
    }

    /**
     *
     */
    private function _calcNeighbors() {
        $deltas = array(
            array(-1, 0),  // up
            array(1, 0),   // down
            array(0, -1),  // left
            array(0, 1),   // right
            array(-1, -1), // up left
            array(-1, 1),  // up right
            array(1, -1),  // down left
            array(1, 1),   // down right
        );
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->columns; $j++) {
                $mines = 0;
                foreach ($deltas as $delta) {
                    $newRow = $i + $delta[0];
                    $newColumn = $j + $delta[1];
                    if ($this->_outOfBound($newRow + 1, $newColumn + 1)) {
                        continue;
                    }
                    $newIdx = $this->_calcIndex($newRow, $newColumn);
                    /** @var Cell $tObj */
                    $tObj = $this->grid[$newIdx];
                    if ($tObj->hasMine()) {
                        $mines++;
                    }
                }
                $index = $this->_calcIndex($i, $j);
                /** @var Cell $tmpObj */
                $tmpObj = &$this->grid[$index];
                $tmpObj->setNeighbors($mines);
            }
        }
    }

    /**
     * @param $row : count from 0
     * @param $column : count from 0
     * @return int
     */
    private function _calcIndex($row, $column): int {
        return ($row) * $this->columns + ($column);
    }

    /**
     * @param $row : count from 1
     * @param $column : count from 1
     * @return bool
     */
    private function _outOfBound($row, $column): bool {
        if ($row < 1 ||
            $column < 1 ||
            $row > $this->rows ||
            $column > $this->columns
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function __toString(): string {
        $r = "";
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->columns; $j++) {
                $index = $this->_calcIndex($i, $j);
                /** @var Cell $tmpObj */
                $tmpObj = $this->grid[$index];
                if ($tmpObj->hasMine()) {
                    $r .= sprintf("* ");
                    continue;
                }
                if ($tmpObj->isVisible()) {
                    $r .= sprintf("- ");
                    continue;
                }
                $r .= sprintf("%d ", $tmpObj->getNeighbors());
            }
            $r .= sprintf("\n");
        }
        return $r;
    }

    public function getVisible(): array {
        $result = array();
        for ($i = 0; $i < count($this->grid); $i++) {
            /** @var Cell $tmpObj */
            $tmpObj = &$this->grid[$i];
            if ($tmpObj->hasShovel()) {
                array_push($result, -2); // shovel
            } else if ($tmpObj->isVisible()) {
                array_push($result, $tmpObj->getNeighbors()); // visible, show neighbors
            }else{
                array_push($result, -1); // invisible
            }
        }
        return $result;
    }

    public function getAll(): array {
        $result = array();
        for ($i = 0; $i < count($this->grid); $i++) {
            /** @var Cell $tmpObj */
            $tmpObj = &$this->grid[$i];
            if ($tmpObj->hasMine()) {
                array_push($result, -3); // mines
            } else {
                array_push($result, $tmpObj->getNeighbors());
            }
        }
        return $result;
    }

    /**
     * @param $row : count from 1
     * @param $column : count from 1
     *
     * @return int
     *      2 already visible
     *      1 out of range
     *      0 ok
     *     -1 boom...
     */
    public function click($row, $column): int {
        if ($this->_outOfBound($row, $column)) {
            return 1;
        }
        $index = $this->_calcIndex($row - 1, $column - 1);
        /** @var Cell $tmpObj */
        $tmpObj = $this->grid[$index];

        if ($tmpObj->isVisible()) {
            return 2;
        }

        if ($tmpObj->hasMine()) {
            return -1;
        } else {
            if ($tmpObj->getNeighbors() == 0) {
                $this->_clickRelatedSpace($row, $column);
            } else {
                $tmpObj->setVisible();
                $this->processedNum++;
            }
            return 0;
        }
    }

    /**
     * @param $row : count from 1
     * @param $column : count from 1
     */
    private function _clickRelatedSpace($row, $column) {
        $stk = array(array($row - 1, $column - 1));
        $deltas = array(
            array(-1, 0), // up
            array(1, 0),  // down
            array(0, -1), // left
            array(0, 1),  // right
        );
        while (!empty($stk)) {
            $v = array_pop($stk);
            $index = $this->_calcIndex($v[0], $v[1]);
            /** @var Cell $tObj */
            $tObj = &$this->grid[$index];

            //echo sprintf("(%s, %s), hasNeighbor=%s,  ", $v[0], $v[1], $tObj->getNeighbors());

            $tObj->setVisible();
            $this->processedNum++;

            //echo "pushing: ";

            foreach ($deltas as $delta) {
                $newRow = $v[0] + $delta[0];
                $newColumn = $v[1] + $delta[1];
                if ($this->_outOfBound($newRow + 1, $newColumn + 1)) {
                    continue;
                }
                $index = $this->_calcIndex($newRow, $newColumn);
                $tObj = &$this->grid[$index];
                if ($tObj->getNeighbors() == 0 && !$tObj->isVisible()) {
                    array_push($stk, array($newRow, $newColumn));
                    //echo sprintf("(%s, %s) ", $newRow, $newColumn);
                }
            }
            //echo "\n";
        }
    }

    /**
     * @param $row
     * @param $column
     *
     * @return int
     *      3 recycled a shovel
     *      2 already visible
     *      1 out of range
     *      0 ok
     *     -1 ran out of shovel
     */
    public function putShovel($row, $column): int {
        if ($this->_outOfBound($row, $column)) {
            return 1;
        }
        $index = $this->_calcIndex($row - 1, $column - 1);
        /** @var Cell $tmpObj */
        $tmpObj = $this->grid[$index];

        if ($tmpObj->hasShovel()) {
            $tmpObj->removeShovel();
            $this->shovels ++;
            $this->processedNum --;
            return 3;
        }

        if (!$this->shovels > 0){
            return -1;
        }

        if ($tmpObj->isVisible()) {
            return 2;
        }

        $tmpObj->putShovel();
        $this->shovels --;
        $this->processedNum++;
        return 0;
    }

    /**
     * @return bool
     */
    public function hasFinished(): bool {
        /** @var Cell $v */
        foreach($this->grid as $v){
            if (!$v->isVisible() && !$v->hasShovel()){
                return false;
            }
        }
        return true;
/*        if ($this->processedNum >= $this->rows * $this->columns) {
            return true;
        }
        return false;*/
    }

    /**
     * @return mixed
     */
    public function getRows() {
        return $this->rows;
    }

    /**
     * @return mixed
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * @return int
     */
    public function getShovels(): int {
        return $this->shovels;
    }


}
