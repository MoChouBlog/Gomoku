var my = true; //我的ID 一但设置就不改变 黑为true 白为false
var gameMode = false;//游戏类型 true为人人 false为人机
var Begin = false;//游戏开始
var complexity = 0;//机器人等级
var me; // ture 是该黑棋下 false是白棋下
var over;//游戏结束

layer.confirm('请选择模式？', {
    btn: ['人机', '人人'],
    closeBtn: 0,
}, function (index, layero) {
    gameMode = false;//人机对战
    $('.choose').animate({
        opacity: 1
    }, 800)
    $('.choose_shade').animate({
        opacity: 0.6
    }, 800)
    layer.close(index);
}, function (index) {
    gameMode = true;//人人对战
    connect();
    $('.choose_shade').animate({
        opacity: 0,
        'z-index': -1
    }, 800)
    $('.choose').animate({
        opacity: 0,
        'z-index': -1
    }, 800)
});


//机器人等级
$('.levl').click(function () {
    var index = $(this).index() + 1;
    complexity = index;
    $('.choose').animate({
        opacity: 0,
        'z-index': -1
    })
    $('.choose_shade').animate({
        opacity: 0,
        'z-index': -1
    })
    connect();
    gameBegin();
});

var canWidth = Math.min(500, $(window).width() - 30);
var canHeight = canWidth;

var chess = document.getElementById('chess');
var context = chess.getContext('2d');

var chess_board = document.getElementById('chess_board');
var context2 = chess_board.getContext('2d');

var chess_pointer = document.getElementById('chess_pointer');
var context3 = chess_pointer.getContext('2d');


$('#chess_pointer').css({
    width: canWidth,
    height: canHeight
});
$('#chess').css({
    width: canWidth,
    height: canHeight
});
$('#chess_board').css({
    width: canWidth,
    height: canHeight
});
$('.checkerboard').css({
    width: canWidth,
    height: canHeight
});

var utilW = canWidth / 15;
var utilH = utilW;

var init = function () {
    context.clearRect(0, 0, 450, 450);
    context3.clearRect(0, 0, 450, 450);
    me = true;
    Begin = false;
    over = false;
    $('#tips').html('等待玩家进入');
};

init();

var gameBegin = function () {
    Begin = true;
    if (me == my)
        $('#tips').html('游戏开始,我先行,我是黑棋');
    else
        $('#tips').html('游戏开始,对手先行，我是白棋');
};

//加载完后画棋盘
context2.strokeStyle = "#dfdfdf";
onload = function () {
    drawChessBoard();
};

var drawChessBoard = function () {
    for (var i = 0; i < 15; i++) {
        context2.moveTo(15 + i * 30, 15);
        context2.lineTo(15 + i * 30, 435);
        context2.stroke();

        context2.moveTo(15, 15 + i * 30);
        context2.lineTo(435, 15 + i * 30);
        context2.stroke();
    }
};


//画棋子
var oneStep = function (i, j, me) {
    context3.clearRect(0, 0, 450, 450);

    context.beginPath();
    context.arc(15 + i * 30, 15 + j * 30, 13, 0, 2 * Math.PI);
    context.closePath();
    var gradient = context.createRadialGradient(15 + i * 30 + 2, 15 + j * 30 - 2, 13, 15 + i * 30 + 2, 15 + j * 30 - 2, 0);
    if (me) {
        gradient.addColorStop(0, "#0a0a0a");
        gradient.addColorStop(1, "#636766");
    } else {
        gradient.addColorStop(0, "#C7C7C7");
        gradient.addColorStop(1, "#f9f9f9");
    }
    context.fillStyle = gradient;
    context.fill();
};


//鼠标点击
chess.onclick = function (e) {
    //判断是否是自己 不是自己就不落棋
    // if (my != me || Begin != true)
    //     return;
    e.preventDefault();
    var x = e.offsetX;
    var y = e.offsetY;
    ws.send('{"event":"move","roomId":' + roomId + ',"my":' + my + ',"x":' + Math.floor(x / utilW) + ',"y":' + Math.floor(y / utilW) + '}');
};


var gameover = function (win) {
    var title;
    if (win == 1) {
        title = "黑子赢了";
    } else if (win == 2) {
        title = "白子赢了";
    } else if (win == 8) {
        title = "棋盘已满";
    }
    over = true;
    ws.close();
    layer.open({
        title: title,
        content: '亲，再来一局试试！',
        btn: ['嗯', '不要'],
        yes: function (index) {
            //location.reload();
            init();
            connect();
            layer.close(index);
        }
    });

};

var moveLater = function (x, y) {
    if (over) {
        return;
    }

    oneStep(x, y, me);

    if (gameMode) {
        if (me != my) {
            if (my)
                $('#tips').html('该你黑棋了，我看好你');
            else
                $('#tips').html('该你白棋了，我看好你');
        } else
            $('#tips').html('对手走棋');

    } else {
        if (me != my) {
            if (my)
                $('#tips').html('该你黑棋了，我看好你');
            else
                $('#tips').html('该你白棋了，我看好你');
        } else {
            $('#tips').html('电脑走棋，走着瞧');
        }
    }
    me = !me;
};

/*
layer.open({
    title: '玩家退出',
    content: '亲，请重新等待！',
    btn: ['嗯'],
});
*/

