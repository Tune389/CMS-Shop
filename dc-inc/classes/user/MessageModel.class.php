<?php

class MessageModel
{

    public $content,
        $subject,
        $receiver_id,
        $sender_id,
        $date,
        $id;

    public function __construct($data)
    {
        if (empty($data->sender_email)) {
            $data->sender_email = $data->sender_email_empty;
            $data->sender_name = $data->sender_email_empty;
        }
        foreach ($data as $varname => $content) {
            $this->$varname = $content;
        }
    }

    public function delete_inbox_sender()
    {
        Db::nrquery('UPDATE messages SET outbox = 0 WHERE id =' . $this->id);
    }

    public function delete_inbox_receiver()
    {
        Db::nrquery('UPDATE messages SET inbox = 0 WHERE id =' . $this->id);
    }

    public function delete()
    {
        if ($this->sender_id == $_SESSION['userid']) {
            $this->delete_inbox_sender();
        } else {
            $this->delete_inbox_receiver();
        }
    }

    public function get_message()
    {
        return get_object_vars(this);
    }

    public function set_message_read()
    {
        Db::nrquery('UPDATE messages SET opened = 1 WHERE id = ' . $this->id);
    }
}