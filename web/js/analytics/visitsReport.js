import { initBaseData, renderBootstrapElemnts, setAreas, setOrgs, setOrgsByDistricts } from "./helpers.js"
const mainForm = document.querySelector('#visits_report_form'),
    submitBtn = document.querySelector('#btn-ok')
const XLSDownLoad = document.querySelector('#xls')
const tableArea = document.querySelector('#ListRecs')
const areaSelector = document.querySelector('#areas-selector'),
    districtSelector = document.querySelector('#districts-selector'),
    orgSelector = document.querySelector('#organizations-selector'),
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
    if (excelValue.value == "1") fetch(`${window.API_URL}/v3/analytics/visit/report`, {
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
            a.download = 'Общий отчет по приемам.xlsx'
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
            tableArea.innerHTML = `<div class="p-3">Отчет сгенерирован и скачан.</div>`
        })
    else fetch(`${window.API_URL}/v3/analytics/visit/report`, {
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
            const { count, areas } = json.data
            tableArea.innerHTML = `
        <table class="report__table">
            <thead class="report__table-header">
                <tr class="report__table-row">
                    <th class="report__table-header" rowspan="2">Канал записи</th>
                    <th class="report__table-header important-col" colspan="${count.length}">Статус записей</th>
                </tr>
                <tr class="report__table-row">
                    ${count.map(col => `<th class="report__table-header">${col.name}</th>`).join('')}
                </tr>
            </thead>
            <tbody class="report__table-body">
                <tr class="report__table-row">
                <td class="report__table-col">Итого</td>
                    ${count.map(col => `<td class="report__table-col">${col.count}</td>`).join('')}
                </tr>
            ${areas.map(area => `
                <tr class="report__table-row">
                    <td class="report__table-col important-col" colspan="${count.length + 1}">${area.name}</td>
                </tr>
                ${area.districts.map(district => `
                <tr class="report__table-row">
                    <td class="report__table-col important-col" colspan="${count.length + 1}">${district.name}</td>
                </tr>
                ${district.organizations.map(org => `
                <tr class="report__table-row">
                    <td class="report__table-col important-col" colspan="${count.length + 1}">${org.name}</td>
                </tr>
                ${org.channels.map(channel => `
                <tr class="report__table-row">
                    <td class="report__table-col">${channel.name}</td>
                    ${count.map(col => `<td class="report__table-col">${channel.count.find(channelCol => channelCol.id == col.id).count}</td>`).join('')}
                </tr>
                `).join('')}
                <tr class="report__table-row">
                    <td class="report__table-col important-col">Итого по организации</td>
                    ${count.map(col => `<td class="report__table-col">${org.count.find(orgCol => orgCol.id == col.id).count}</td>`).join('')}
                </tr>
                `).join('')}
                <tr class="report__table-row">
                    <td class="report__table-col important-col">Итого по району</td>
                    ${count.map(col => `<td class="report__table-col">${district.count.find(districtCol => districtCol.id == col.id).count}</td>`).join('')}
                </tr>
                `).join('')}
                <tr class="report__table-row">
                    <td class="report__table-col important-col">Итого по округу</td>
                    ${count.map(col => `<td class="report__table-col">${area.count.find(areaCol => areaCol.id == col.id).count}</td>`).join('')}
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