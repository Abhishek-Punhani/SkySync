import axios from "axios";

export function getFiles(url){
    return axios
        .get(url, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            responseType: 'json',
        })
        .then((response) => {
            console.log(response);
            return response.data;
        })
        .catch((error) => {
            console.log(error);
            return error;
        });
}
