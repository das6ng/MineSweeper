<?php
/**
 * Created by PhpStorm.
 * User: Dash
 * Date: 2019/3/12
 * Time: 14:58
 */

session_start();

require "MineSweeperCore.php";

if (!isset($_POST['type'])) {
    $result = array(
        'status' => 'error',
        'message' => 'no type defined',
    );
    echo json_encode($result);
    die();
}

if ($_POST['type'] == "start") {
    if (!isset($_POST['rows']) || !isset($_POST['columns']) || !isset($_POST['mines'])) {
        $result = array(
            'status' => 'error',
            'message' => 'incomplete start parameters',
        );
        echo json_encode($result);
        die();
    }
    $rows = $_POST['rows'];
    $columns = $_POST['columns'];
    $mines = $_POST['mines'];
    if ($rows < 1 || $columns < 1) {
        $result = array(
            'status' => 'error',
            'message' => 'wrong row count or column count',
        );
        echo json_encode($result);
        die();
    }
    if ($rows > 100 || $columns > 100) {
        $result = array(
            'status' => 'error',
            'message' => 'too big row count or column count',
        );
        echo json_encode($result);
        die();
    }
    if ($mines < 1 || $mines > $rows * $columns) {
        $result = array(
            'status' => 'error',
            'message' => 'wrong mine count',
        );
        echo json_encode($result);
        die();
    }

    if (isset($_COOKIE['uid']) && isset($_SESSION[$_COOKIE['uid']])) {
        /** @var \Dash\MineSweeper $sweeper */
        $sweeper = unserialize($_SESSION[$_COOKIE['uid']]);
        //error_log("\n".$sweeper."\n",3,'D:\\Wnmp\\logs\\php_errors.log');
        $result = array(
            'status' => 'continue',
            'rows' => $sweeper->getRows(),
            'columns' => $sweeper->getColumns(),
            'shovels' => $sweeper->getShovels(),
            'grid' => $sweeper->getVisible(),
            'message' => "continue play the game",
        );
        echo json_encode($result);
        die();
    }

    $uid = uniqid();
    setcookie('uid', $uid);
    $sweeper = new \Dash\MineSweeper($rows, $columns);
    $sweeper->init($mines);
    $_SESSION[$uid] = serialize($sweeper);
    error_log("uid: ".$uid."\n".$sweeper."\n",3,'D:\\Wnmp\\logs\\php_errors.log');

    $result = array(
        'status' => 'ok',
        'rows' => $sweeper->getRows(),
        'columns' => $sweeper->getColumns(),
        'shovels' => $sweeper->getShovels(),
        'grid' => $sweeper->getVisible(),
        'message' => "successfully start game",
    );
    echo json_encode($result);
    die();
}

if (!isset($_COOKIE['uid']) || !isset($_SESSION[$_COOKIE['uid']])) {
    $result = array(
        'status' => 'error',
        'message' => "no game playing now",
    );
    echo json_encode($result);
    die();
}
$uid = $_COOKIE['uid'];
/** @var \Dash\MineSweeper $sweeper */
$sweeper = unserialize($_SESSION[$uid]);
//error_log("\n".$sweeper."\n",3,'D:\\Wnmp\\logs\\php_errors.log');
switch ($_POST['type']) {
    case 'click':
        if (!isset($_POST['row']) || !isset($_POST['column'])) {
            $result = array(
                'status' => 'error',
                'message' => 'incomplete click parameters',
            );
            echo json_encode($result);
            die();
        }
        $row = $_POST['row'];
        $column = $_POST['column'];

        $re = $sweeper->click($row, $column);
        $result = array();
        switch ($re){
            case 2:
                $result = array(
                    'status' => 'error',
                    'message' => 'already visible',
                );
                break;
            case 1:
                $result = array(
                    'status' => 'error',
                    'message' => 'out of range',
                );
                break;
            case 0:
                $result = array(
                    'status' => 'ok',
                    'rows' => $sweeper->getRows(),
                    'columns' => $sweeper->getColumns(),
                    'shovels' => $sweeper->getShovels(),
                    'grid' => $sweeper->getVisible(),
                    'message' => 'successfully clicked',
                );
                $_SESSION[$uid] = serialize($sweeper);
                if ($sweeper->hasFinished()){
                    $result['status'] = 'finish';
                    unset($_SESSION[$uid]);
                }
                break;
            case -1:
                $result = array(
                    'status' => 'over',
                    'rows' => $sweeper->getRows(),
                    'columns' => $sweeper->getColumns(),
                    'shovels' => $sweeper->getShovels(),
                    'grid' => $sweeper->getAll(),
                    'message' => 'game over',
                );
                //error_log("\n".implode(' ',$result['grid'])."\n",3,'D:\\Wnmp\\logs\\php_errors.log');
                unset($_SESSION[$uid]);
                break;
            default:
                $result = array(
                    'status' => 'error',
                    'message' => 'unknown over',
                );
        }
        echo json_encode($result);
        die();
        break;

    case 'shovel':
        if (!isset($_POST['row']) || !isset($_POST['column'])) {
            $result = array(
                'status' => 'error',
                'message' => 'incomplete click parameters',
            );
            echo json_encode($result);
            die();
        }
        $row = $_POST['row'];
        $column = $_POST['column'];

        $re = $sweeper->putShovel($row, $column);
        $result = array();
        switch ($re){
            case 3:
                $result = array(
                    'status' => 'recycle',
                    'rows' => $sweeper->getRows(),
                    'columns' => $sweeper->getColumns(),
                    'shovels' => $sweeper->getShovels(),
                    'message' => 'recycled a shovel',
                );
                $_SESSION[$uid] = serialize($sweeper);
                break;
            case 2:
                $result = array(
                    'status' => 'error',
                    'message' => 'already visible',
                );
                break;
            case 1:
                $result = array(
                    'status' => 'error',
                    'message' => 'out of range',
                );
                break;
            case 0:
                $result = array(
                    'status' => 'ok',
                    'rows' => $sweeper->getRows(),
                    'columns' => $sweeper->getColumns(),
                    'shovels' => $sweeper->getShovels(),
                    'message' => 'successfully put shovel',
                );
                $_SESSION[$uid] = serialize($sweeper);
                if ($sweeper->hasFinished()){
                    $result['status'] = 'finish';
                    unset($_SESSION[$uid]);
                }
                break;
            case -1:
                $result = array(
                    'status' => 'error',
                    'shovels' => $sweeper->getShovels(),
                    'message' => 'ran out of shovel',
                );
                break;
            default:
                $result = array(
                    'status' => 'error',
                    'message' => 'unknown over',
                );
        }
        echo json_encode($result);
        die();
        break;
    default:
        $result = array(
            'status' => 'error',
            'message' => 'wrong request type',
        );
        echo json_encode($result);
        die();
}
