var scope = 'order';
cx.bind("delete", function (deleteIds) {
    if (confirm(
        cx.variables.get('TXT_CONFIRM_DELETE_ORDER', scope)+'\n'+ cx.variables.get('TXT_ACTION_IS_IRREVERSIBLE', scope)
    )) {
        var stockUpdate = false;
        if (confirm(
            cx.variables.get('TXT_SHOP_CONFIRM_RESET_STOCK', scope)
        )) {
            stockUpdate = true;
        }
        window.location.replace(
            "?deleteids=" + encodeURI(deleteIds)  + (stockUpdate ? '&update_stock=1' : '')
            + "&csrf=" + cx.variables.get('CSRF_PARAM', scope) + "&vg_increment_number=0"
        );
    }
}, 'order');

// Function to overwrite delete onclick event. See BackendController $option['functions]['onclick']['delete']
function deleteOrder(deleteUrl) {
    if (confirm(
        cx.variables.get('TXT_CONFIRM_DELETE_ORDER', scope)+'\n'+ cx.variables.get('TXT_ACTION_IS_IRREVERSIBLE', scope)
    )) {
        var stockUpdate = false;
        if (confirm(
            cx.variables.get('TXT_SHOP_CONFIRM_RESET_STOCK', scope)
        )) {
            stockUpdate = true;
        }
        window.location.replace(deleteUrl + (stockUpdate ? '&update_stock=1' : ''));
    }
}
var scope = 'shopDelete';
cx.bind("delete", function (deleteIds) {
    if (confirm(
        cx.variables.get('TXT_CONFIRM_DELETE', scope)+'\n'+ cx.variables.get('TXT_ACTION_IS_IRREVERSIBLE', scope)
    )) {
        window.location.replace(
            "?deleteids=" + encodeURI(deleteIds) + "&csrf=" + cx.variables.get('CSRF_PARAM', scope) + "&vg_increment_number=0"
        );
    }
}, 'shopDelete');

cx.bind('activate', function (entityIds) {
    updateShopStatus(entityIds, 1);
}, 'shopActivate');

cx.bind('deactivate', function (entityIds) {
    updateShopStatus(entityIds, 0);
}, 'shopDeactivate');

function updateShopStatus(entityIds, newStatus) {
    for (let i = 0; i < entityIds.length; i++) {
        const statusElement = cx.jQuery('.vg-function-status[data-entity-id="' + entityIds[i] + '"]');
        if (statusElement.attr('data-status-value') == newStatus) {
            continue;
        }
        cx.ajax(
            'Html',
            'updateStatus',
            {
                type: 'POST',
                data: {
                    'entityId': entityIds[i],
                    'newStatus': newStatus,
                    'statusField': 'discountActive',
                    'component': 'Shop',
                    'entity': 'Product',
                },
                showMessage: true,
                beforeSend: function() {
                    cx.jQuery(statusElement).addClass('loading');
                },
                success: function(json) {
                    if (newStatus) {
                        cx.jQuery(statusElement).addClass('active');
                    } else {
                        cx.jQuery(statusElement).removeClass('active');
                    }
                },
                preError: function(xhr, status, error) {
                    cx.tools.StatusMessage.showMessage(error);
                    cx.jQuery(this).data('status-value', (cx.jQuery(this).hasClass('active') ? 0 : 1));
                },
                complete: function() {
                    cx.jQuery(statusElement).removeClass('loading');
                }
            },
            cx.variables.get('frontendLocale', 'contrexx')
        );
    }
}

function toggle_header()
{
    if (typeof document.getElementsByName('headerOn')[0] == 'undefined') {
        return;
    }

    if (document.getElementsByName('headerOn')[0]) {
        var disp = (document.getElementsByName('headerOn')[0].checked ? "block" : "none");
        document.getElementById('headerLeft').parentNode.parentElement.parentElement.style.display = disp;
    }
}

function toggle_footer()
{
    if (typeof document.getElementsByName('footerOn')[0] == 'undefined') {
        return;
    }

    if (document.getElementsByName('footerOn')[0]) {
        var disp = (document.getElementsByName('footerOn')[0].checked ? "block" : "none");
        document.getElementById('footerLeft').parentNode.parentElement.parentElement.style.display = disp;
    }
}

function toggle_categories(status)
{
    var check = jQuery('#category-all:checked').length > 0;
    if(status && !check){
        return;
    }
    jQuery('.category input').prop('checked', check);
}

jQuery(document).ready(function($){
    // Add class to all orders with pending status
    cx.jQuery('.order-status select option:selected[value="0"]').closest('tr').addClass('pending');

    $('.category').change(function(){
        var check = true;
        $('.category').each(function(){
            if(!$(this).find('input').is(":checked")){
                check = false;
            }
            $('#category-all').prop('checked', check);
        });
    });
    $('#category-all').change(function(){
        toggle_categories(false);
    });
    $('#form-0-headerOn').change(function(){
        toggle_header();
    });
    $('#form-0-footerOn').change(function(){
        toggle_footer();
    });
    toggle_header();
    toggle_footer();
    toggle_categories(true);

    if (typeof document.getElementsByName('headerOn')[0] != 'undefined') {
        document.getElementById('headerRight').value = document.getElementById('form-0-headerRight').value;

        document.getElementById('headerRight').onchange = function () {
            document.getElementById('form-0-headerRight').value = document.getElementById('headerRight').value
        };
    }

    if (typeof document.getElementsByName('footerOn')[0] != 'undefined') {
        document.getElementById('footerRight').value = document.getElementById('form-0-footerRight').value;

        document.getElementById('footerRight').onchange = function () {
            document.getElementById('form-0-footerRight').value = document.getElementById('footerRight').value
        };
    }

    cx.jQuery('.parent').click(function() {
        if (cx.jQuery(this).hasClass('open')) {
            cx.jQuery(this).find('input').attr('checked', false);
            cx.jQuery(this).removeClass('open');
        } else {
            cx.jQuery(this).find('input').attr('checked', true);
            cx.jQuery(this).addClass('open');
        }
    })
});

function updateDefault(defaultEntity) {
    cx.jQuery(".adminlist tbody tr").has('.id').each(function(index, el) {
        var entity = cx.jQuery(el);
        if (parseInt(cx.jQuery(defaultEntity).closest('tr').find('.id').text()) != parseInt(entity.find('.id').text())) {
            entity.find('.default input').removeProp('checked');
        }
    });
}

function updateCurrencyCode(selectElement)
{
    var currency_increment = cx.variables.get('CURRENCY_INCREMENT', 'currency');
    console.log(selectElement.options[selectElement.selectedIndex].text);
    var code = selectElement.value;
    var name = selectElement.options[selectElement.selectedIndex].text;
    // By default, the name is followed by the code in parentheses
    name = name.replace(/\s*\([A-Z]+\)$/, "");
    var increment = currency_increment[code];
    console.log("code "+code+", name: "+name+", increment: "+increment);
    document.getElementsByName("symbol")[0].value = code;
    document.getElementsByName("name")[0].value = name;
    document.getElementsByName("increment")[0].value = increment;
}

function updateExchangeRates(selectedElement)
{
    // The Currency ID
    var indexSelected = selectedElement.value;
    console.log(indexSelected);
    // The ID of the previous standard Currency.
    // This can only be determined if its rate is exactly 1.
    var indexStandard = -1;
    // The factor used to calculate the new exchange rates
    var standardFactor = document.getElementsByName("rate-"+indexSelected)[0].value;
//alert("standard factor: "+standardFactor);
    // If no standard factor can be determined, use 1.
    // This means that none of the rates are actually changed, except for
    // the new standard currency, whose rate is always set to 1.
    if (standardFactor <= 0) {
        standardFactor = 1;
    }
    // Find all input elements
    var arrInputElement = document.getElementsByTagName('input');
//alert("element: "+arrInputElement+", length: "+arrInputElement.length);
    // Array to store all exchange rate elements,
    // except for the new standard currency.
    var arrRateElement = new Array();
    // Loop through the input elements
    for (var i = 0; i < arrInputElement.length; ++i) {
        var element = arrInputElement[i];
        // Skip non currency rate fields
        if (!element.name.match(/^rate\-(\d+)/)) {
            continue;
        }
        // The index, aka Currency ID
        var index = RegExp.$1
        // Nothing to do for the new standard currency here.
        if (index == indexSelected) {
            continue;
        }
        // Any currency having a rate of 1 is considered as
        // being the previous standard.

        if (element.value == 1) {
            indexStandard = index;
//alert("standard: i: "+i+", value: "+element.value);
        }
        // Remember those rate elements that need to be updated.
        arrRateElement.push(element);
//alert("i: "+i+", rate: "+arrInputElement[i].value);
    }
    // Only if there was a previous standard currency with a rate of 1...

    if (indexStandard != -1) {
        for (var i = 0; i < arrRateElement.length; ++i) {
            element = arrRateElement[i];
            // ...update the other rates
            element.value = (element.value / standardFactor).toFixed(6);
        }
    }
    // Finally, set the new standard currency rate to 1.
    document.getElementsByName("rate-"+indexSelected)[0].value = "1.000000";
    return true;
}

function swapBlock(div)
{
    const oldClass = (document.getElementById(div).classList.contains('hide') ? 'hide' : 'show');
    const newClass = (document.getElementById(div).classList.contains('hide') ? 'show' : 'hide');
    document.getElementById(div).classList.remove(oldClass);
    document.getElementById(div).classList.add(newClass);
}
