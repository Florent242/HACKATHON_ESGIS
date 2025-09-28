/**
 * 
 * @param {string} tagName 
 * @param {object} attribut 
 * @returns {HTMLElement}
 */
export function createEle(tagName,attributes ={}){
    const element = document.createElement(tagName);
    for (const [attribute,value] of Object.entries(attributes)) {
        if(value !== null){
            element.setAttribute(attribute,value);
        }
    }
    return element;
}
