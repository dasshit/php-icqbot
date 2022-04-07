<?php

namespace Dasshit\ICQBot\Events {

    enum EventsType: string
    {
        case NEW_MESSAGE = "newMessage";
        case EDITED_MESSAGE = "editedMessage";
        case DELETED_MESSAGE = "deletedMessage";
        case PINNED_MESSAGE = "pinnedMessage";
        case UNPINNED_MESSAGE = "unpinnedMessage";
        case NEW_CHAT_MEMBER = "newChatMembers";
        case LEFT_CHAT_MEMBER = "leftChatMembers";
        case CALLBACK_QUERY = "callbackQuery";
    }
}