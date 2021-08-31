<?php

namespace App\Http\Presenters;

class CommentPresenter
{
    public static function user(array $comments)
    {
        $result = [];

        for ($i = 0; $i < count($comments); $i++) {
            $comment = $comments[$i];
            $comment['user'] = [
                'id' => $comment['user_id'],
                'name' => $comment['user_name']
            ];
            unset($comment['user_id']);
            unset($comment['user_name']);
            $result[] = $comment;
        }

        return $result;
    }
}
