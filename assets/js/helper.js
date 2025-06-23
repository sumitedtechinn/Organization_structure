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