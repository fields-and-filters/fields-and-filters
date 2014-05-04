;
(function ($, $faf) {
    $(document).ready(function(){
        $('.faf-filters-dropdown').on('click', 'label', function(e){
            var $form = $(this).parents($faf.selector('form')),
                $group = $(this).parents($faf.selector('group')),
                $input = $(this).siblings($faf.selector('input')),
                select = 'faf-selected';

            if ($input.is(':checked')) {
                $group.removeClass(select);
            } else {
                $group.addClass(select);
            }

            $input.attr('checked', !$input.is(':checked'));

            $form.trigger('submit');

            return false;
        });
    });
})(jQuery, jQuery.fieldsandfilters)