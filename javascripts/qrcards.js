$(document).ready(() => {
    $('.link-remove-from-print').on('click', function(e) {
        e.stopPropagation()
        $(this).parents('.qrcard-to-print').remove()
        return false
    })
})