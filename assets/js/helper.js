async function getMethod(url) {
    try {
        let response = await fetch(url, {
            method : "GET",         
        });
        if(!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.text();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function postMethodWithTextResponse(url,params) {
    try {
        let response = await fetch(url, {
            method : "POST",         
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.text();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

async function postMethodWithJsonResponse(url,params) {
    try {
        let response = await fetch(url,{
            method : "POST" , 
            body : JSON.stringify(params)
        });
        if(!response.ok) {
            console.error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;   
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
}

const makeContent = (content) => `<span class="truncate-label" data-bs-toggle="tooltip" title="${content}">${content}</span>`;

const updateButton = (id) => `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" onclick = "updateDetails(${id})"><i class="bi bi-pencil-fill"></i></div>`;

const updateDisabledButton = () => `<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Disabled"><i class="bi bi-pencil-fill"></i></div>`;

const deleteButton = (id,table,funName) => `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" onclick = "${funName}(${id},'${table}')"><i class="bi bi-trash-fill"></i></div>`;

const deleteDisabledButton = () => `<div data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Disabled"><i class="bi bi-trash-fill"></i></div>`;

const restoreButton = (id,table) => `<div class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Re-store" onclick = "restoreDetails(${id},'${table}')"><i class="fadeIn animated bx bx-sync" style = "font-size:larger;"></i></div>`;

const paramanentDeleteButton = (id,table) => `<div class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Parmanent Delete" onclick = "parmanentDeleteDetails(${id},'${table}')"><i class="bi bi-trash-fill"></i></div>`;