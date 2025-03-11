import { initBaseData, renderBootstrapElemnts, setAreas, setOrgs, setOrgsByDistricts } from "./helpers.js"
const mainForm = document.querySelector('#price_services_report_form')
const XLSDownLoad = document.querySelector('#xls')
const tableArea = document.querySelector('#ListRecs')
const areaSelector = document.querySelector('#areas-selector'),
    orgSelector = document.querySelector('#organizations-selector'),
    districtSelector = document.querySelector('#districts-selector'),
    submitBtn = document.querySelector('#btn-ok'),
    excelValue = document.querySelector('#excel-value')
const areas = [],
    districts = [],
    orgs = []


$('#areas-selector').on('change', function () {
    orgSelector.innerHTML = setOrgs(orgs, $(this).val().map(item => +item))
    districtSelector.innerHTML = setOrgs(districts, $(this).val().map(item => +item))
    renderBootstrapElemnts(['organizations', 'districts'])
    $('#districts-selector').trigger('change')
    $('#organizations-selector').trigger('change')
})

$('#districts-selector').on('change', function () {
    orgSelector.innerHTML = setOrgsByDistricts(orgs, $(this).val().map(item => +item))
    renderBootstrapElemnts(['organizations'])
    $('#organizations-selector').trigger('change')
})

initBaseData()
    .then(data => {
        if (!data) return
        areas.push(...data.areas)
        districts.push(...data.districts)
        orgs.push(...data.organizations)
        areaSelector.innerHTML = setAreas(areas)
        districtSelector.innerHTML = setOrgs(districts, areas.map(area => area.id))
        orgSelector.innerHTML = setOrgs(orgs, areas.map(area => area.id))
        renderBootstrapElemnts(['areas', 'districts', 'organizations'])
    })
mainForm.addEventListener('submit', e => {
    e.preventDefault()
    submitBtn.disabled = true
    XLSDownLoad.disabled = true
    tableArea.innerHTML = `<div class="loading"></div>`
    const data = new FormData(e.target)
    data.delete('areas')
    data.append('areas', JSON.stringify(Array.from(areaSelector.selectedOptions).map(option => option.value)))
    data.delete('districts')
    data.append('districts', JSON.stringify(Array.from(districtSelector.selectedOptions).map(option => option.value)))
    data.delete('organizations')
    data.append('organizations', JSON.stringify(Array.from(orgSelector.selectedOptions).map(option => option.value)))
    if (excelValue.value == "1") fetch(`${window.API_URL}/v3/analytics/service-price/report`, {
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
            a.download = 'Отчёт об оказании ветеринарных услуг.xlsx'
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
            tableArea.innerHTML = `<div class="p-3">Отчет сгенерирован и скачан.</div>`
        })
    else fetch(`${window.API_URL}/v3/analytics/service-price/report`, {
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
            const { columns, areas } = json.data
            tableArea.innerHTML = `
        <table class="report__table">
            <thead class="report__table-header">
                <tr class="report__table-row">
                    <th class="report__table-corner" rowspan="3">Название услуги</th>
                    <th class="report__table-col" rowspan="3">${columns[0].name}</th>
                    <th class="report__table-col" rowspan="3">${columns[1].name}</th>
                    <th class="report__table-col" rowspan="3">${columns[2].name}</th>
                    <th class="report__table-col" rowspan="3">${columns[3].name}</th>
                    <th class="report__table-col" colspan="8">Количество ветеринарных услуг, оказанных в рамках госзадания</th>
                </tr>
                <tr class="report__table-row">
                    <th class="report__table-col" rowspan="2">${columns[4].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[5].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[6].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[7].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[8].name}</th>
                    <th class="report__table-col" colspan="3">Оформленные ВСД</th>
                </tr>
                <tr class="report__table-row">
                    <th class="report__table-col" rowspan="2">${columns[9].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[10].name}</th>
                    <th class="report__table-col" rowspan="2">${columns[11].name}</th>
                </tr>
            </thead>
            <tbody class="report__table-body">
                <tr class="report__table-row">
                    <td class="report__table-col" colspan="2">Итого</td>
                    ${columns.map(column => column.id != 'price' ? `
                    <td class="report__table-col">${column.value}</td>
                    ` : ``).join('')}
                </tr>
                ${areas.map(area => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${columns.length}">
                            ${area.name}
                        </td>
                    </tr>
                    ${area.districts.map(district => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${columns.length}">
                            ${district.name ? district.name : 'Район не указан'}
                        </td>
                    </tr>
                    ${district.organizations.map(org => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col" colspan="${columns.length}">
                            ${org.name}
                        </td>
                    </tr>
                    ${org.services.map(service => `
                    <tr class="report__table-row">
                        <td class="report__table-col important-col"">
                            ${service.name}
                        </td>
                        ${service.columns.map(column =>  `
                        <td class="report__table-col">${column.value}</td>
                        ` ).join('')}
                    </tr>
                    `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col" colspan="2">Итого по организации</td>
                        ${org.columns.map(column => column.id != 'price' ? `
                        <td class="report__table-col">${column.value}</td>
                        ` : ``).join('')}
                    </tr>
                    `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col" colspan="2">Итого по району</td>
                        ${district.columns.map(column => column.id != 'price' ? `
                        <td class="report__table-col">${column.value}</td>
                        ` : ``).join('')}
                    </tr>
                    `).join('')}
                    <tr class="report__table-row">
                        <td class="report__table-col" colspan="2">Итого по округу</td>
                        ${area.columns.map(column => column.id != 'price' ? `
                        <td class="report__table-col">${column.value}</td>
                        ` : ``).join('')}
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