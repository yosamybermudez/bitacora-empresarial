// MetroUI . Hint
$('[title]').each(function () {
    var elem = $(this);
    var title = elem.attr('title');
    elem.attr('data-role', 'hint');
    elem.attr('data-hint-position', 'bottom');
    elem.addClass('c-help');
    elem.attr('data-hint-text', title);
    elem.attr('data-cls-hint','bg-cyan fg-white drop-shadow');
    elem.removeAttr('title');
});