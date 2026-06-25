import {usePage} from '@inertiajs/vue3';

export function subscribeToChannel(channelId,listen, callback) {
    if (!channelId) {
        console.error('Channel ID is required to subscribe to channel');
        return { unsubscribe: () => {} };
    }
    const channel = window.Echo.private(channelId);
    channel.listen(listen, (event) => {
        if (callback && typeof callback === 'function') {
            callback(event);
        }
    });

    return {
        unsubscribe: () => {
            channel.stopListening('.chat.new_message');
        }
    };
}

export function subscribeToUserChannel(callback) {
    const user = usePage().props.auth?.user;

    if (!user) {
        console.error('User is not authenticated');
        return { unsubscribe: () => {} };
    }

    const channelName = `user.${user.id}`;
    const channel = window.Echo.private(channelName);

    channel.listen('.message.sent', (event) => {
        if (callback && typeof callback === 'function') {
            callback(event.message);
        }
    });

    return {
        unsubscribe: () => {
            channel.stopListening('.message.sent');
        }
    };
}

export function subscribeToClientChannel(callback) {
    const client = usePage().props.auth?.client;

    if (!client) {
        console.error('Client is not authenticated');
        return { unsubscribe: () => {} };
    }

    const channelName = `client.${client.id}`;
    const channel = window.Echo.private(channelName);

    channel.listen('.message.sent', (event) => {
        if (callback && typeof callback === 'function') {
            callback(event.message);
        }
    });

    return {
        unsubscribe: () => {
            channel.stopListening('.message.sent');
        }
    };
}

export function subscribeToAuthChannel(callback) {
    const user = usePage().props.auth?.user;
    const client = usePage().props.auth?.client;

    if (user) {
        return subscribeToUserChannel(callback);
    } else if (client) {
        return subscribeToClientChannel(callback);
    } else {
        console.error('No authenticated entity found');
        return { unsubscribe: () => {} };
    }
}
