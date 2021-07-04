function jankx_detect_post_format_is_changed() {
    var jkx_post_formats = document.getElementById('jankx-post-formats');
    if (!jkx_post_formats) {
        return;
    }

    var targets = jankx_post_formats.gutenberg_active
        ? [document.getElementById('post-format-selector-0')]
        : document.querySelectorAll('#post-formats-select input[name="post_format"]');

    for (i = 0; i < targets.length; i += 1) {
        targets[i].addEventListener('change', function(e){
            console.log(e.target.value);
        });
    }
}

if (!jankx_post_formats.gutenberg_active) {
    window.addEventListener('DOMContentLoaded', jankx_detect_post_format_is_changed);
} else {
    var checkExist = setInterval(function() {
        if (document.querySelector('.components-panel .edit-post-post-status')) {
            jankx_detect_post_format_is_changed();
           clearInterval(checkExist);
        }
     }, 100)
}
