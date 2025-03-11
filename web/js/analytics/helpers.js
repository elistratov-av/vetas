export function initBaseData() {
    return fetch(`${window.API_URL}/v3/analytics/dictionary`)
        .then(res => res.json())
        .then(json => (json?.is_success && json.data))
}

export function setAreas(areas) {
    return areas.map(area => `<option value="${area.id}" selected>${area.name}</option>`)
}

export function setOrgs(orgs, selectedAreas = []) {
    return orgs.filter(org => (selectedAreas.includes(org.area_id) || !org.area_id)).map(org => `<option value="${org.id}" selected>${org.name}</option>`)
}

export function setOrgsByDistricts(orgs, selectedDistricts = []) {
    return orgs.filter(org => (selectedDistricts.includes(org.district_id) || !org.district_id)).map(org => `<option value="${org.id}" selected>${org.name}</option>`)
}

export function setSpecialists(specialists, selectedOrgs = []) {
    return specialists.filter(specialist => (selectedOrgs.includes(specialist.organization_id))).map(specialist => `<option value="${specialist.id}" selected>${specialist.name}</option>`)
}

export function setServices(services) {
    return services.map(serviceGroup => `<optgroup label=${serviceGroup.name}>
    ${serviceGroup.services.map(service => `<option value="${service.id}" selected>${service.name} [${service.code}]</option>`)}
    </optgroup>`)
}

export function setStatuses(statuses) {
    return statuses.map(status => `<option value="${status.id}" selected>${status.name}</option>`)
}

export function renderBootstrapElemnts(componentns = []) {
    componentns.forEach(component => {
        $(`#${component}-selector`).multiselect('destroy')
        $(`#${component}-selector`).multiselect({
            enableClickableOptGroups: true,
            nonSelectedText: 'Выберите...',
            allSelectedText: "Выбраны все",
            nSelectedText: "выбрано",
            buttonWidth: '250',
            maxHeight: 200,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            selectAllText: 'Выбрать все',
            includeSelectAllOption: true,
            filterPlaceholder: 'Поиск...',
        })
        $(`#${component}-selector`).multiselect("refresh")
    })

}