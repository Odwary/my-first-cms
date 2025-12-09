$(function(){
    
    console.log('Привет, это старый js ))');
    init_get();
    init_post();
    init_get_new();
    init_post_new();
});

// Обработчик для "Показать продолжение (GET)"
function init_get() 
{
    $('a.ajaxArticleBodyByGet').one('click', function(e){
        e.preventDefault();
        var contentId = $(this).attr('data-contentId');
        console.log('ID статьи (GET) = ', contentId); 
        showLoaderIdentity();
        $.ajax({
            url: 'ajax/showContentsHandler.php?articleId=' + contentId, 
            dataType: 'json',
            method: 'GET'
        })
        .done (function(obj){
            hideLoaderIdentity();
            console.log('Ответ получен (GET)', obj);
            if (obj && obj.content) {
                $('li.' + contentId + ' .content').html(obj.content);
            } else if (typeof obj === 'string') {
                // Если пришел просто текст (старый формат)
                $('li.' + contentId + ' .content').html(obj);
            }
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
            console.log('Ошибка соединения при получении данных (GET)');
        });
        
        return false;
    });  
}

// Обработчик для "Показать продолжение (POST)"
function init_post() 
{
    $('a.ajaxArticleBodyByPost').one('click', function(e){
        e.preventDefault();
        var contentId = $(this).attr('data-contentId');
        console.log('ID статьи (POST) = ', contentId);
        showLoaderIdentity();
        $.ajax({
            url: 'ajax/showContentsHandler.php', 
            dataType: 'text',
            method: 'POST',
            data: {
                articleId: contentId
            }
        })
        .done (function(content){
            hideLoaderIdentity();
            console.log('Ответ получен (POST)', content);
            $('li.' + contentId + ' .content').html(content);
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('Ошибка соединения с сервером (POST)');
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
        });
        
        return false;
    });  
}

// Обработчик для "(GET) -- NEW"
function init_get_new() 
{
    $('a.ajaxGetNew').one('click', function(e){
        e.preventDefault();
        var contentId = $(this).attr('data-contentId');
        if (!contentId) {
            // Пытаемся извлечь ID из href
            var href = $(this).attr('href');
            var match = href.match(/articleId=(\d+)/);
            if (match) {
                contentId = match[1];
            }
        }
        console.log('ID статьи (GET NEW) = ', contentId); 
        showLoaderIdentity();
        $.ajax({
            url: 'ajax/showContentsHandler.php?articleId=' + contentId, 
            dataType: 'json',
            method: 'GET'
        })
        .done (function(obj){
            hideLoaderIdentity();
            console.log('Ответ получен (GET NEW)', obj);
            if (obj && obj.content) {
                $('li.' + contentId + ' .content').html(obj.content);
            } else if (typeof obj === 'string') {
                // Если пришел просто текст (старый формат)
                $('li.' + contentId + ' .content').html(obj);
            }
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
            console.log('Ошибка соединения при получении данных (GET NEW)');
        });
        
        return false;
    });  
}

// Обработчик для "(POST) -- NEW"
function init_post_new() 
{
    $('a.ajaxPostNew').one('click', function(e){
        e.preventDefault();
        var contentId = $(this).attr('data-contentId');
        if (!contentId) {
            // Пытаемся извлечь ID из href
            var href = $(this).attr('href');
            var match = href.match(/articleId=(\d+)/);
            if (match) {
                contentId = match[1];
            }
        }
        console.log('ID статьи (POST NEW) = ', contentId);
        showLoaderIdentity();
        $.ajax({
            url: 'ajax/showContentsHandler.php', 
            dataType: 'text',
            method: 'POST',
            data: {
                articleId: contentId
            }
        })
        .done (function(content){
            hideLoaderIdentity();
            console.log('Ответ получен (POST NEW)', content);
            $('li.' + contentId + ' .content').html(content);
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('Ошибка соединения с сервером (POST NEW)');
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
        });
        
        return false;
    });  
}
