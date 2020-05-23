<?php

class Room
{
    //赢法数组
    public $wins = [];
    //赢法种类索引
    public $count = 0;

    //赢法统计数组
    public $myWin = [];
    public $computerWin = [];

    //落棋
    public $chessBoard = [];

    //机器人等级
    public $complexity = 3;//1到3 3最高级别

    //当前走棋者
    public $me = true;

    //游戏中
    public $gameRun = false;

    //落棋数量
    public $downcount = 0;

    //游戏模式
    public $gameMode = true;//true为人人 false为人机

    public function __construct()
    {
        //赢法种类数组
        for ($i = 0; $i < 15; $i++) {
            $this->wins[$i] = [];
            for ($j = 0; $j < 15; $j++) {
                $this->wins[$i][$j] = [];
            }
        }

        for ($i = 0; $i < 15; $i++) {//横线赢法
            for ($j = 0; $j < 11; $j++) {
                for ($k = 0; $k < 5; $k++) {
                    $this->wins[$i][$j + $k][$this->count] = true;
                }
                $this->count++;
            }
        }

        for ($i = 0; $i < 15; $i++) {//竖线赢法
            for ($j = 0; $j < 11; $j++) {
                for ($k = 0; $k < 5; $k++) {
                    $this->wins[$j + $k][$i][$this->count] = true;
                }
                $this->count++;
            }
        }

        for ($i = 0; $i < 11; $i++) {//斜线赢法
            for ($j = 0; $j < 11; $j++) {
                for ($k = 0; $k < 5; $k++) {
                    $this->wins[$i + $k][$j + $k][$this->count] = true;
                }
                $this->count++;
            }
        }

        for ($i = 0; $i < 11; $i++) {//反斜线赢法
            for ($j = 14; $j > 3; $j--) {
                for ($k = 0; $k < 5; $k++) {
                    $this->wins[$i + $k][$j - $k][$this->count] = true;
                }
                $this->count++;
            }
        }


        //赢法统计数组
        for ($i = 0; $i < $this->count; $i++) {
            $this->myWin[$i] = 0;
            $this->computerWin[$i] = 0;
        }

        //落棋初始化
        for ($i = 0; $i < 15; $i++) {
            $this->chessBoard[$i] = [];
            for ($j = 0; $j < 15; $j++) {
                $this->chessBoard[$i][$j] = 0;
            }
        }
    }


    public function AI()
    {

        $myScore = [];

        $computerScore = [];

        $max = 0;

        $u = 0;
        $v = 0;

        for ($i = 0; $i < 15; $i++) {
            $myScore[$i] = [];
            $computerScore[$i] = [];
            for ($j = 0; $j < 15; $j++) {
                $myScore[$i][$j] = 0;
                $computerScore[$i][$j] = 0;
            }
        }
        for ($i = 0; $i < 15; $i++) {
            for ($j = 0; $j < 15; $j++) {
                if ($this->chessBoard[$i][$j] == 0) {
                    for ($k = 0; $k < $this->count; $k++) {
                        if (@$this->wins[$i][$j][$k]) {
                            if ($this->complexity == 1) {
                                if ($this->myWin[$k] == 1) {
                                    $myScore[$i][$j] += 200;
                                } else if ($this->myWin[$k] == 2) {
                                    $myScore[$i][$j] += 400;
                                } else if ($this->myWin[$k] == 3) {
                                    $myScore[$i][$j] += 2000;
                                } else if ($this->myWin[$k] == 4) {
                                    $myScore[$i][$j] += 10000;
                                }

                                if ($this->computerWin[$k] == 1) {
                                    $computerScore[$i][$j] += 100;
                                } else if ($this->computerWin[$k] == 2) {
                                    $computerScore[$i][$j] += 300;
                                } else if ($this->computerWin[$k] == 3) {
                                    $computerScore[$i][$j] += 600;
                                } else if ($this->computerWin[$k] == 4) {
                                    $computerScore[$i][$j] += 14000;
                                }
                            } else if ($this->complexity == 2) {
                                if ($this->myWin[$k] == 1) {
                                    $myScore[$i][$j] += 200;
                                } else if ($this->myWin[$k] == 2) {
                                    $myScore[$i][$j] += 400;
                                } else if ($this->myWin[$k] == 3) {
                                    $myScore[$i][$j] += 2000;
                                } else if ($this->myWin[$k] == 4) {
                                    $myScore[$i][$j] += 10000;
                                }

                                if ($this->computerWin[$k] == 1) {
                                    $computerScore[$i][$j] += 300;
                                } else if ($this->computerWin[$k] == 2) {
                                    $computerScore[$i][$j] += 500;
                                } else if ($this->computerWin[$k] == 3) {
                                    $computerScore[$i][$j] += 2100;
                                } else if ($this->computerWin[$k] == 4) {
                                    $computerScore[$i][$j] += 15000;
                                }
                            } else if ($this->complexity == 3) {

                                if ($this->myWin[$k] == 1) {
                                    $myScore[$i][$j] += 200;
                                } else if ($this->myWin[$k] == 2) {
                                    $myScore[$i][$j] += 400;
                                } else if ($this->myWin[$k] == 3) {
                                    $myScore[$i][$j] += 2000;
                                } else if ($this->myWin[$k] == 4) {
                                    $myScore[$i][$j] += 10000;
                                }
                                if ($this->computerWin[$k] == 1) {
                                    $computerScore[$i][$j] += 210;
                                } else if ($this->computerWin[$k] == 2) {
                                    $computerScore[$i][$j] += 850;
                                } else if ($this->computerWin[$k] == 3) {
                                    $computerScore[$i][$j] += 3000;
                                } else if ($this->computerWin[$k] == 4) {
                                    $computerScore[$i][$j] += 80000;
                                }
                            }

                        }
                    }
                    if ($myScore[$i][$j] > $max) {
                        $max = $myScore[$i][$j];
                        $u = $i;
                        $v = $j;
                    } else if ($myScore[$i][$j] == $max) {
                        if ($computerScore[$i][$j] > $computerScore[$u][$v]) {
                            $u = $i;
                            $v = $j;
                        }
                    }
                    if ($computerScore[$i][$j] > $max) {
                        $max = $computerScore[$i][$j];
                        $u = $i;
                        $v = $j;
                    } else if ($computerScore[$i][$j] == $max) {
                        if ($myScore[$i][$j] > $myScore[$u][$v]) {
                            $u = $i;
                            $v = $j;
                        }
                    }
                }
            }
        }
        if ($this->chessBoard[$u][$v] != 0) {
            //计算不出最优解，所以直接找剩下的可下的就是了

            for ($i = 0; $i < 15; $i++) {
                for ($j = 0; $j < 15; $j++) {
                    if ($this->chessBoard[$i][$j] == 0)
                        break 2;
                }
            }
            $u = $i;
            $v = $j;
        }
        if (!$this->downcount) {
            $u = 7;
            $v = 7;
        }

        return [
            "x" => $u,
            "y" => $v
        ];
    }

    public function moveLater($i, $j)
    {
        if ($this->chessBoard[$i][$j] == 0) {
            if ($this->me) {
                $this->chessBoard[$i][$j] = 1;
                for ($k = 0; $k < $this->count; $k++) {
                    if (@$this->wins[$i][$j][$k]) {
                        $this->myWin[$k]++;
                        $this->computerWin[$k] = 6;
                        if ($this->myWin[$k] == 5) {
                            //黑棋胜利;
                            $this->gameRun = false;
                            return [
                                "state" => 2,
                                "type" => 1,
                                "message" => "黑棋胜利"
                            ];
                        }
                    }
                }
            } else {
                $this->chessBoard[$i][$j] = 2;
                for ($k = 0; $k < $this->count; $k++) {
                    if (@$this->wins[$i][$j][$k]) {
                        $this->computerWin[$k]++;
                        $this->myWin[$k] = 6;
                        if ($this->computerWin[$k] == 5) {
                            //白棋胜利
                            $this->gameRun = false;
                            return [
                                "state" => 2,
                                "type" => 2,
                                "message" => "白棋胜利"
                            ];
                        }
                    }
                }
            }

            //改变下一个走棋者
            $this->me = !$this->me;
            $this->downcount++;
            if ($this->downcount >= 15 * 15) {
                $this->gameRun = false;
                return [
                    "state" => 0,
                    "type" => 8,
                    "message" => "棋盘已满游戏结束"
                ];
            }
            return [
                "state" => 1,
                "type" => 1,
                "message" => "落棋成功 x:".$i." y:".$j
            ];

        } else {
            //该格被占
            return [
                "state" => 0,
                "type" => 5,
                "message" => "该格被占"
            ];
        }

    }

}





