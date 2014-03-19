var data;
var work = false;
var do_next;
var interval;


$(document).ready(function(){
    
    
    $.ajaxSetup({
        url: 'do.php',
        type: 'POST',
        dataType: 'json',
        beforeSend: function() {
            $('.ajax').addClass('loading');
        },
        complete: function() {
            $('.ajax').removeClass('loading');
        }
    });


    $('.btn-test').click(function(e, test){
        
        data = false;
        
        type = $('[name="s_type"]:checked').val();
        kw = $('#keyword').val();
        
        if (typeof type === 'undefined') {
            alert('Chưa chọn search type!');
            return;
        }
        
        if (kw === '') {
            alert('Chưa nhập từ khóa!');
            return;
        }
        
        data = true;
        
        if (test) {
            return;
        }
        
        $.ajax({
            data: {action: 'test'},
            success: function(res) {
                msg('Thời gian để lấy 1 thông tin vào khoảng <strong>' + res + '</strong> giây');
            }
        });
    });
    
    
    $('.btn-search').click(function(){
        
        $('.btn-test').trigger('click', true);
        
        work = true;
        
        if (data) {
            
            type = $('[name="s_type"]:checked').val();
            kw = $('#keyword').val();
            
            $.ajax({
                data: {action: 'search', type: type, keyword: kw},
                success: function(res) {
                    if (res.result === 'sess') {
                        
                        if (res.total > 0) {
                            $('#keyword').attr('data-id', res.value);
                            $('.msg').html('Đã xử lý ' + res.count + '/' + res.total + ' (' + Math.round(res.count * 100/res.total) + '%)');
                        }
                        else {
                            $('.msg').html('Không có kết quả nào cho yêu cầu tìm kiếm này!');
                        }
                        
                        if (res.count < res.total) {
                            search();
                        }
                    }
                }
            });
        }
        
    });
    
    
    $('.btn-s-continue').click(function(){
        
        slf = $('[name="sess"]:checked');
        
        sess = slf.parent();
        s_id = slf.val();
        s_type = sess.attr('data-type');
        s_kw = sess.attr('data-kw');
        
        if (typeof s_id === 'undefined') {
            alert('Chưa chọn session nào!');
            return;
        }
        
        if (slf.siblings('span').hasClass('label-success')) {
            if (!confirm('Phần nội dung này đã được tải hết! Vẫn muốn tiếp tục?')) {
                return;
            }
        }
        
        $('[name="s_type"]').eq(s_type - 1).prop('checked', true);
        $('#keyword').val(s_kw).attr('data-id', s_id);
    })
    
    
    $('.btn-s-stop').click(function(){
        if (work) {
            if (!confirm('Do you want to stop this task!')) {
                return;
            }
            work = false;
            $('#keyword').removeAttr('data-id');
            msg('SEARCH sẽ STOP khi tiến trình đang chạy được hoàn tất!');
        }
    });


    $('.btn-export-excel').click(function(){
        s_id = get_sess();
        if (!s_id) {
            alert('Chưa chọn session nào!');
            return;
        }

        $.ajax({
            data: {action: 'export', sess: s_id},
            success: function(res) {
                if (res.result === 'ok') {
                    window.location.href = res.filename;
                }
                else if (res.result === 'fail') {
                    alert('Chưa có danh sách cho phiên dữ liệu này!');
                }
            }
        });
    });


    $('.btn-sess-reload').click(function(){
        $.ajax({
            data: {action: 'sess_list'},
            dataType: 'html',
            success: function(res) {
                $('.sess-list').html(res);
            }
        });
    });


    $('.btn-delete-sess').click(function(){
        s_id = get_sess();
        if (!s_id) {
            alert('Chưa chọn session nào!');
            return;
        }

        if (!confirm('Do you want to delete this session?')) {
            return;
        }

        $.ajax({
            data: {action: 'delete', sess: s_id},
            success: function(res) {
                if (res.result === 'ok') {
                    $('[name="sess"]:checked').parent().remove();
                }
            }
        });
    });

});


function get_sess() {
    s_id = $('[name="sess"]:checked').val();

    return (typeof s_id !== 'undefined') ? s_id : false;
}


function search()
{    
    sess = $('#keyword').attr('data-id');
    
    $.ajax({
        async: false,
        data: {action: 'search', sess: sess},
        success: function(res) {
            
            if (!work) {
                do_next = null;
                return;
            }
            
            if (res.result === 'process') {
                $('.msg').html('Đã xử lý ' + res.count + '/' + res.total + ' (' + Math.round(res.count * 100/res.total) + '%)');
                    
                aj = this;
                do_next = setTimeout(function(){
                    $.ajax(aj);
                }, 2000);
            }
            else if (res.result === 'finish') {
                $('.msg').html('Đã xử lý hết ' + res.total + ' thông tin!');
            }
            else if (res.result === 'retry') {
                aj = this;
                do_next = setTimeout(function(){
                    $.ajax(aj);
                }, 2000);
            }
        }
    });
}


function msg(content, time)
{
    interval = null;
    
    if (typeof time === 'undefined') {
        time = 10000;
    }
    
    $('.msg').html(content);
    interval = setTimeout(function(){
        $('.msg').html('');
    }, time);
}