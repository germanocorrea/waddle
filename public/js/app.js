window.board = {}
var app = new Vue({
    el: '#maindrag',
    data: {
        columns: []
    },
    methods: {
        openMail: function(event) {
            var target = (event.target.id == "" ? event.target.parentElement : event.target)
            $.get('/mail/' + target.id).then(function(result) {
                html = '<h3>' + result.mail.subject + '</h3>'
                html += '<small>' + result.mail.from + '</small>'
                html += '<hr>'
                html += result.mail.html
                $('#mailmodal').html(html).foundation('open')
            })
        },
        dragStart: function(event) {
            window.board.currentParent = $(event.target).parent().parent().attr('id')
            event.dataTransfer.setData("text/plain", event.target.id)
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
            window.board.newParent = ($(event.target).is("li")) ? event.target.parentElement.id : event.target.id
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
    $.ajax({
        method: "GET",
        url: "/getmails"
    }).done(function(data) {
        window.board.data = data
        updateBoard()
    })
})

function updateBoard() {
    app.columns = [];
    $(window.board.data.columns).each(function() {
        app.columns.push(this)
    })
    sendBoardToServer()
}

function changeCardColumn(event) {
    app.columns[window.board.newParent].cards[event.dataTransfer.getData("text/plain")] = app.$set(app.columns[window.board.newParent], event.dataTransfer.getData("text/plain"), window.board.data.columns[window.board.currentParent].cards[event.dataTransfer.getData("text/plain")])
    delete app.columns[window.board.currentParent].cards[event.dataTransfer.getData("text/plain")]

}

function sendBoardToServer() {
    $.ajax({
        method: "GET",
        url: "/savedata",
        dataType: 'json',
        data: window.board.data
    }).then(function(data) {
        console.log(data)
    }, function(data) {
        console.log(data)
    })
}

function syncfromserver(event) {
    swal({
        title: 'Sincronizar Emails do Provedor',
        text: 'Isso pode demorar um pouco :/',
        showCancelButton: true,
        confirmButtonText: 'Faça!',
        showLoaderOnConfirm: true,
        preConfirm: function(email) {
            return new Promise(function(resolve) {
                return $.get("/syncmails").then(function() {
                    swal({
                        type: 'success',
                        title: 'Emails sincronizados!',
                    })
                }, function() {
                    swal({
                        type: 'error',
                        title: 'Alguma coisa deu muito errado!'
                    })
                })
            })
        },
        allowOutsideClick: false
    })
}
