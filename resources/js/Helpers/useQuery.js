export function useQuery() {
    const params = new URLSearchParams(window.location.search);

    function get(name,isInt=false) {
        if (isInt) {
            return getInt(name);
        }
        const value = params.get(name);
        return value ? value : null;
    }

    function getInt(name) {
        const value = parseInt(get(name));
        if(value === 0) {
            return 0;
        }
        return value ? value : null;
    }

    function getArray(name) {
        const value = [];
        for (const [key, val] of params.entries()) {
            if(key.slice(0, key.indexOf("[")) == name)
                value.push(val);
        }
        // console.log('value :>> ', value);
        return value ;
    }

    function getArrayInt(name) {
        let value = getArray(name);
        if(Array.isArray(value)) {
            return value ? value.map((n) => parseInt(n)) : null;
        }
        value = isNaN(parseInt(value)) ? null : [parseInt(value)];
        return value;
    }
    return {get, getInt, getArray, getArrayInt, params};
}
