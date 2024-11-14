function datatableColumnShowDetailsList(e) {
    return `<a href="javascript:void(0)" class="btn btn-usm btn-dark btn-icon btn-circle show-details">
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
