import axios from 'axios';

export function getFiles(url) {
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

export function postFiles(url, data) {
    return axios
        .post(url, data, {
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
