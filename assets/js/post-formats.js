var j = window.jankx_post_formats;

function jankx_post_formats_toogle_metadata(target, format, data = {}) {
    // Render current format after DOM loaded
    var parentWrap = target.findParent('#jankx-post-format-metadata');

    if (format && j.templates[format] !== false && ['standard', 0].indexOf(format) < 0) {
        var prepareData = typeof j.default_values[format] === 'object'
                ? Object.assign(j.default_values[format], j.data)
                : data;
        target.innerHTML = tim(j.templates[format], prepareData);
        parentWrap.style.display = 'block';
    } else {
        target.innerHTML = '';
        parentWrap.style.display = 'none';
    }
}

function jankx_detect_post_format_is_changed() {
    var jkx_post_formats_ele = document.getElementById('jankx-post-formats');
    if (!jkx_post_formats_ele) {
        return;
    }

    jankx_post_formats_toogle_metadata(jkx_post_formats_ele, j.current_format, j.data);

    var targets = j.is_block_editor
        ? [document.getElementById('post-format-selector-0')]
        : document.querySelectorAll('#post-formats-select input[name="post_format"]');

    for (i = 0; i < targets.length; i += 1) {
        targets[i].addEventListener('change', function(e) {
            post_format = e.target.value;
            jankx_post_formats_toogle_metadata(
                jkx_post_formats_ele,
                post_format,
                j.current_format === post_format ? j.data : []
            );
        });
    }
}

if (!j.is_block_editor) {
    window.addEventListener('DOMContentLoaded', jankx_detect_post_format_is_changed);
} else {
    var checkExist = setInterval(function() {
        if (document.querySelector('.components-panel .edit-post-post-status')) {
            clearInterval(checkExist);

           jankx_detect_post_format_is_changed();
        }
     }, 100)
}
