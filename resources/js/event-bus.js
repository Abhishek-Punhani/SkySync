import mitt from 'mitt';
export const emitter = mitt();

// topics

// show-error -- takes error message
//file-upload  -- takes files to upload
// show-success-notif -- takes success message
// show-notif --takes type(success, error,warning) and message
