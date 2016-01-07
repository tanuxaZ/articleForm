var max_img_size = 2000000; //байт
var max_img_size_mb = max_img_size / 1000000; // М

var img_dimantions = ["jpeg", "png"];

/**
 * Валидирует картинку
 *
 * @param img_id - идентификтор поля input[type=file]
 * @param result_container_id - идентификатор контейнера, куда показывать картинку
 */
var validateImg = function(imgId, resultContainerId){
    $('form').on('change', '#'+imgId, function(){
        var fileInput = document.getElementById(imgId);
        var fileDisplayArea = document.getElementById(resultContainerId);
        var file = fileInput.files[0];
        var fileSize = file.size;
        var found = false;
        var errors = new Array();

        img_dimantions.forEach(function(extension) {
            if (file.type.match('image/'+extension)) {
                found = true;
            }
        })
        if (!found) {
            errors[errors.length] = 'Разрешены только файлы с разширениями: '+ img_dimantions.join(', ');
        }

        if (fileSize > max_img_size) {
            errors[errors.length] = 'Размер изображения не должен привышать '+ max_img_size_mb + ' М';
        }

        if (errors.length == 0) {
            var reader = new FileReader();

            reader.onload = function(e) {
                fileDisplayArea.innerHTML = "";

                var img = new Image(400);
                img.src = reader.result;

                fileDisplayArea.appendChild(img);
                $('#'+imgId).next().val(reader.result);
            }

            reader.readAsDataURL(file);
        } else {
            var input = $("#"+imgId);
            input.replaceWith(input.val('').clone(true));
            fileDisplayArea.innerHTML = errors.join('<br>');
        }
    });
}

/**
 * Валидация формы на заполнение обязательных полей
 *
 * @param formObj - объект формы
 */
var validateRegisterForm = function(formObj){
    var isErr = 0;

    formObj.find('.error').text('');

    formObj.find('.ClsRequired').each(function(){
        if ($.trim($(this).val()).length == 0) {
            isErr = 1;
            $(this).parent().next().text('Поле обязательно для заполнения');
        }
    })

    if (isErr == 1) {
        return;
    }

    $('form').submit();
}

$(function() {
    $('#datepicker').datetimepicker({
        format:'Y-m-d H:i',
        lang:'ru'
    });

    validateImg('fileImg', 'fileDisplayArea');

    $('form input[name=save]').click(function() {
        validateRegisterForm($(this).parents('form'));
    })
});
