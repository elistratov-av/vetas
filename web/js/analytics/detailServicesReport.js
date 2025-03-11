import { initBaseData, renderBootstrapElemnts, setAreas, setOrgs, setServices, setSpecialists } from "./helpers.js"
const mainForm = document.querySelector('#detail_services_report_form')
const XLSDownLoad = document.querySelector('#xls')
const tableArea = document.querySelector('#ListRecs')
const areaSelector = document.querySelector('#areas-selector'),
    orgSelector = document.querySelector('#organizations-selector'),
    specialistSelector = document.querySelector('#specialists-selector'),
    servicesSelector = document.querySelector('#services-selector'),
    typeSelector = document.querySelector('#types-selector'),
    channelSelector = document.querySelector('#channels-selector'),
    speciesSelector = document.querySelector('#species-selector'),
    submitBtn = document.querySelector('#btn-ok'),
    excelValue = document.querySelector('#excel-value')
const areas = [],
    orgs = [],
    specialists = [],
    services = [],
    types = [],
    channels = [],
    species = []


$('#areas-selector').on('change', function () {
    orgSelector.innerHTML = setOrgs(orgs, $(this).val().map(item => +item))
    renderBootstrapElemnts(['organizations'])
    $('#organizations-selector').trigger('change')
})

$('#organizations-selector').on('change', function () {
    specialistSelector.innerHTML = setSpecialists(specialists, $(this).val().map(item => +item))
    renderBootstrapElemnts(['specialists'])
})

initBaseData()
    .then(data => {
        if (!data) return
        areas.push(...data.areas)
        orgs.push(...data.organizations)
        specialists.push(...data.specialists)
        services.push(...data.services)
        types.push(...data.types)
        channels.push(...data.channels)
        species.push(...data.species)
        areaSelector.innerHTML = setAreas(areas)
        orgSelector.innerHTML = setOrgs(orgs, areas.map(area => area.id))
        specialistSelector.innerHTML = setSpecialists(specialists, orgs.map(org => +org.id))
        servicesSelector.innerHTML = setServices(services)
        typeSelector.innerHTML = setAreas(types)
        channelSelector.innerHTML = setAreas(channels)
        speciesSelector.innerHTML = setAreas(species)
        renderBootstrapElemnts(['areas', 'organizations', 'specialists', 'services', 'channels', 'types', 'species'])
    })
mainForm.addEventListener('submit', e => {
    e.preventDefault()
    submitBtn.disabled = true
    XLSDownLoad.disabled = true
    tableArea.innerHTML = `<div class="loading"></div>`
    const data = new FormData(e.target)
    data.delete('areas')
    data.append('areas', JSON.stringify(Array.from(areaSelector.selectedOptions).map(option => option.value)))
    data.delete('organizations')
    data.append('organizations', JSON.stringify(Array.from(orgSelector.selectedOptions).map(option => option.value)))
    data.delete('specialists')
    data.append('specialists', JSON.stringify(Array.from(specialistSelector.selectedOptions).map(option => option.value)))
    data.delete('services')
    data.append('services', JSON.stringify(Array.from(servicesSelector.selectedOptions).map(option => option.value)))
    data.delete('types')
    data.append('types', JSON.stringify(Array.from(typeSelector.selectedOptions).map(option => option.value)))
    data.delete('channels')
    data.append('channels', JSON.stringify(Array.from(channelSelector.selectedOptions).map(option => option.value)))
    data.delete('species')
    data.append('species', JSON.stringify(Array.from(speciesSelector.selectedOptions).map(option => option.value)))
    if (excelValue.value == "1") fetch(`${window.API_URL}/v3/analytics/service/detail-report`, {
        method: 'post',
        body: data
    })
        .then(response => response.blob())
        .then(blob => {
            submitBtn.disabled = false
            XLSDownLoad.disabled = false
            const url = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'Детальный отчет по услугам.xlsx'
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
            tableArea.innerHTML = `<div class="p-3">Отчет сгенерирован и скачан.</div>`
        })
    else fetch(`${window.API_URL}/v3/analytics/service/detail-report`, {
        method: 'post',
        body: data
    })
        .then(res => res.json())
        .then(json => {
            submitBtn.disabled = false
            XLSDownLoad.disabled = false
            if (!json.is_success) {
                tableArea.innerHTML = `<div class="p-3">Ошибка загрузки.</div>`
                return
            }
            const { count, statuses, areas } = json.data
            tableArea.innerHTML = `
        <table class="report__table">
            <thead class="report__table-header">
                <tr class="report__table-row">
                    <th class="report__table-corner main" rowspan="2">Название услуги</th>
                    <th class="report__table-corner" rowspan="2">Канал записи</th>
                    <th class="report__table-header" colspan="${statuses.length + 1}">Статус записей</th>
                </tr>
                <tr class="report__table-row">
                    ${statuses.map(status => `
                        <th class="report__table-col">${status.name}</th>
                    `).join('')}
                    <th class="report__table-col">Итого</th>
                </tr>
            </thead>
            <tbody class="report__table-body">
                <tr class="report__table-row">
                <td class="report__table-col" colspan="2">Итого</td>
                ${statuses.map(status => `
                    <td class="report__table-col">${status.count}</td>
                `).join('')}
                <td class="report__table-col">${count}</td>
                </tr>
                ${areas.map(area => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${statuses.length + 3}">${area.name}</td>
                    </tr>
                    ${area.organizations.map(organization => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${statuses.length + 3}">${organization.name}</td>
                    </tr>
                    <tr class="report__table-row">
                        ${organization.channels.map(channel => `
                            ${channel.services.map(service => `
                            <tr class="report__table-row">
                                <td class="report__table-col">${service.name}</td>
                                <td class="report__table-col">${channel.name}</td>
                                ${statuses.map(status => `
                                <td class="report__table-col">${service.statuses.find(sStatus => sStatus.id == status.id).count}</td>
                                `).join('')}
                                <td class="report__table-col">${service.count}</td>
                                
                            </tr>
                            `).join('')}
                        `).join('')}
                        <td class="report__table-col important-col" colspan="2">Итого по организации</td>
                        ${statuses.map(status => `<td class="report__table-col">${organization.statuses.find(sStatus => sStatus.id == status.id).count}</td>`).join('')}
                        <td class="report__table-col">${organization.count}</td>
                    </tr>
                    `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="2">Итого по округу</td>
                        ${statuses.map(status => `<td class="report__table-col">${area.statuses.find(sStatus => sStatus.id == status.id).count}</td>`).join('')}
                        <td class="report__table-col">${area.count}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        `
        })
})

XLSDownLoad.addEventListener('click', e => {
    excelValue.value = 1
    mainForm.dispatchEvent(new Event('submit'))
    excelValue.value = 0
})