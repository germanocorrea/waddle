var Board = {}
var $alert = $('#alertModal') // TODO: fazer este
var $mailView = $('#mailmodal')

var app = new Vue({
    el: '#maindrag',
    data: {
        columns: []
    },
    methods: {
        openMail: function(event) {
            var target = (event.target.id == '' ? event.target.parentElement : event.target)
            $.get('/mail/' + target.id).then(function(result) {
                if (!result.mail) {
                    $alert.html('<p>An error ocurred while getting your email! :c</p>').foundation('open')
                    console.log(result)
                } else {
                    html = '<h3>' + result.mail.subject + '</h3>'
                    html += '<small>' + result.mail.from + '</small>'
                    html += '<hr>'
                    html += result.mail.html
                    $mailView.html(html).foundation('open')
                }
            })
        },
        dragStart: function(event) {
            Board.currentParent = $(event.target).parent().parent().attr('id')
            event.dataTransfer.setData('text/plain', event.target.id)
            $('.dropzone').toggleClass('todrop')
            $('.draghere').show()
        },

        dragEnd: function(event) {
            $('.dropzone').toggleClass('todrop')
            $('.draghere').hide()
        },

        dragOver: function(event) {
            event.preventDefault()
        },

        dragEnter: function(event) {
            $(event.target).toggleClass('maindrop')
            Board.newParent = ($(event.target).is('li')) ? event.target.parentElement.id : event.target.id
            event.preventDefault()
        },

        dragLeave: function(event) {
            $('.maindrop').toggleClass('maindrop')
        },

        dropElement: function(event) {
            event.preventDefault()
            changeCardColumn(event)
            updateBoard()
        },
    }
})

$(function() {
    $(document).foundation()
    $.get('/getmails').done(function(data) {
        Board.data = data
        updateBoard()
    })
})

function updateBoard() {
    app.columns = [];
    $(Board.data.columns).each(function() {
        app.columns.push(this)
    })
    sendBoardToServer()
}

function changeCardColumn(event) {
    app.columns[Board.newParent].cards[event.dataTransfer.getData('text/plain')] = app.$set(app.columns[Board.newParent], event.dataTransfer.getData('text/plain'), Board.data.columns[Board.currentParent].cards[event.dataTransfer.getData('text/plain')])
    delete app.columns[Board.currentParent].cards[event.dataTransfer.getData('text/plain')]

}

function sendBoardToServer() {
    $.ajax({
        method: 'GET',
        url: '/savedata',
        dataType: 'json',
        data: Board.data
    }).then(function(data) {
        console.log(data)
    }, function(data) {
        console.log(data)
    })
}

function syncFromServer(event) {
    swal({
        title: 'Sincronizar Emails do Provedor',
        text: 'Isso pode demorar um pouco :/',
        showCancelButton: true,
        confirmButtonText: 'Faça!',
        showLoaderOnConfirm: true,
        preConfirm: function(email) {
            return new Promise(function(resolve) {
                return $.get('/syncmails').then(function() {
                    $mailView.html('<h1>Email sincronizados!</h1>').foundation('open')
                }, function() {
                    $mailView.html('<h1>Alguma coisa deu muito errado!</h1>').foundation('open')
                })
            })
        },
        allowOutsideClick: false
    })
}
