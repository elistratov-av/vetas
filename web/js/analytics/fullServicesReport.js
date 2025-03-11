import { initBaseData, renderBootstrapElemnts, setAreas, setOrgs, setServices, setSpecialists, setStatuses } from "./helpers.js"
const mainForm = document.querySelector('#full_services_report_form')
const XLSDownLoad = document.querySelector('#xls')
const tableArea = document.querySelector('#ListRecs')
const areaSelector = document.querySelector('#areas-selector'),
    orgSelector = document.querySelector('#organizations-selector'),
    specialistSelector = document.querySelector('#specialists-selector'),
    servicesSelector = document.querySelector('#services-selector'),
    statusSelector = document.querySelector('#statuses-selector'),
    servicePeriod = document.querySelector('#services_period'),
    submitBtn = document.querySelector('#btn-ok'),
    excelValue = document.querySelector('#excel-value')
const areas = [],
    orgs = [],
    specialists = [],
    services = [],
    statuses = []


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
        statuses.push(...data.statuses)
        areaSelector.innerHTML = setAreas(areas)
        orgSelector.innerHTML = setOrgs(orgs, areas.map(area => area.id))
        specialistSelector.innerHTML = setSpecialists(specialists, orgs.map(org => +org.id))
        servicesSelector.innerHTML = setServices(services)
        statusSelector.innerHTML = setStatuses(statuses)
        renderBootstrapElemnts(['areas', 'organizations', 'specialists', 'services', 'statuses'])
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
    data.delete('statuses')
    data.append('statuses', JSON.stringify(Array.from(statusSelector.selectedOptions).map(option => option.value)))
    if (excelValue.value == "1") fetch(`${window.API_URL}/v3/analytics/service/report`, {
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
            a.download = 'Общий отчет по услугам.xlsx'
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
            tableArea.innerHTML = `<div class="p-3">Отчет сгенерирован и скачан.</div>`
        })
    else fetch(`${window.API_URL}/v3/analytics/service/report`, {
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
            const { count, channels, areas } = json.data
            servicePeriod.innerHTML = count
            tableArea.innerHTML = `
        <table class="report__table">
            <thead class="report__table-header">
                <tr class="report__table-row">
                    <th class="report__table-corner" rowspan="2">Название услуги</th>
                    <th class="report__table-header" colspan="${channels.length + 1}">Количество записей</th>
                </tr>
                <tr class="report__table-row">
                    ${channels.map(channel => `
                        <th class="report__table-col">${channel.name}</th>
                    `).join('')}
                    <th class="report__table-col">Итого</th>
                </tr>
            </thead>
            <tbody class="report__table-body">
                <tr class="report__table-row">
                <td class="report__table-col">Итого</td>
                ${channels.map(channel => `
                    <td class="report__table-col">${channel.count}</td>
                `).join('')}
                <td class="report__table-col">${count}</td>
                </tr>
                ${areas.map(area => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${channels.length + 2}">${area.name}</td>
                    </tr>
                    ${area.organizations.map(organization => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${channels.length + 2}">${organization.name}</td>
                    </tr>
                     ${organization.services.map(service => `
                    <tr class="report__table-row">
                        <td class="report__table-col">${service.name}</td>
                        ${channels.map(channel => `<td class="report__table-col">${service.channels.find(sChannel => sChannel.id == channel.id).count}</td>`).join('')}
                        <td class="report__table-col">${service.count}</td>
                    </tr>
                     `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col important-col">Итого по организации</td>
                        ${channels.map(channel => `<td class="report__table-col">${organization.channels.find(sChannel => sChannel.id == channel.id).count}</td>`).join('')}
                        <td class="report__table-col">${organization.count}</td>
                    </tr>
                    `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col important-col">Итого по округу</td>
                        ${channels.map(channel => `<td class="report__table-col">${area.channels.find(sChannel => sChannel.id == channel.id).count}</td>`).join('')}
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