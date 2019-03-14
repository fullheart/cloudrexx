cx.jQuery(document).ready(function() {

    let inputField = cx.jQuery('#form-0-apiKey');

    if (undefined !== inputField.val() && !inputField.val().length) {
        // Show btn
        const btnText = cx.variables.get('TXT_CORE_MODULE_DATA_ACCESS_GENERATE_BTN', 'DataAccess');
        let btn = '<button id="generate-api-key">' + btnText + '</button>';
        inputField.after(btn);

        // Generate API-Key on click.
        cx.jQuery('#generate-api-key').click(function (event) {
            event.preventDefault();

function initializeConditions() {
    let conditionWrapper = cx.jQuery('#form-0-accessCondition');

    if (undefined === conditionWrapper || !conditionWrapper.length) {
        return;
    }

    addEventListenerForConditions(conditionWrapper);
}
