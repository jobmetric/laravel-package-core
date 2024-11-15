function datatableColumnShowDetailsList(e) {
    return `<a href="javascript:void(0)" class="btn btn-usm btn-dark btn-icon btn-circle" onclick="show_details_list_object(this)">
                <i class="la la-plus fs-5"></i>
            </a>`
}

function datatableColumnSingleCheckList(e) {
    return `<div class="form-check form-check-custom form-check-solid">
                <input class="form-check-input check-one" name="ids[]" type="checkbox" value="${e.id}" id="check-single-${e.id}"/>
                <label class="form-check-label ms-0" for="check-single-${e.id}"></label>
            </div>`
}

function datatableColumnStatusList(e) {
    let text = `<div class="align-center">`

    if (e.status) {
        text += `<div class="badge badge-light-success">${localize.language.package_core.components.boolean_status.enable}</div>`
    } else {
        text += `<div class="badge badge-light-danger">${localize.language.package_core.components.boolean_status.disable}</div>`
    }

    text += `</div>`

    return text
}

function datatableColumnOrderingList(e) {
    return `<div class="align-center text-gray-800">${e.ordering}</div>`
}

const show_details_list_object = function(element){
    const tr = $(element).closest('tr')
    const row = dt.row(tr)
    const icon = $(element).find('i')

    dt.rows().every(function () {
        if (this.child.isShown() && this.index() !== row.index()) {
            this.child.hide();
            $(this.node()).removeClass('shown');
            $(this.node()).find('i').removeClass('la-minus').addClass('la-plus')
        }
    })

    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        icon.removeClass('la-minus').addClass('la-plus')
    } else {
        row.child(listShowDetails(row.data())).show();
        tr.addClass('shown');
        icon.removeClass('la-plus').addClass('la-minus')
    }
}
