<?php


namespace App\Repository;


class BaseRepository
{
    protected array $rules;

    public function validateData(object $request, array $merge = [], ?callable $callback = null): array
    {
        $data = $request->validate($this->rules($merge));
        $append_data = $callback ? $callback($request) : [];

        return array_merge($data, $append_data);
    }

    public function rules(array $merge = []): array
    {
        return array_merge($this->rules, $merge);
    }
}
