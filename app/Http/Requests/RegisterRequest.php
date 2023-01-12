<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
//            'name' => 'required|min:3|max:64',
//            'surname' => 'required|min:3|max:64',
////            'email' => 'unique:users',
//            'password' => 'required|min:6|max:64|confirmed',
//            'password_confirmation' => 'required|min:6|max:64',
////            'number' => 'min:3|max:64',
//            'patronymic' => 'required|min:3|max:64',
//            'city' => 'required',
//            'username' => 'unique:users|required',
//            'date_of_birth' => 'required',
        ];

    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }


    public function messages()
    {
        return [
            'name.required' => 'Необходимо ввести имя в поле.',
            'username.required' => 'Необходимо ввести имя пользователяв поле.',
            'name.min' => 'Имя должно быть не менее 3 символов.',
            'name.max' => 'Имя не должно быть длиннее 64 символов.',
            'surname.required' => 'Поле фамилия обязательно.',
            'patronymic.required' => 'Поле Отчество обязательно.',
            'patronymic.min' => 'Поле Отчество должно быть не менее 3 символов.',
            'patronymic.max' => 'Поле Отчество не должно быть длиннее 64 символов.',
            'surname.min' => 'Фамилия должна быть не менее 3 символов.',
            'surname.max' => 'Длина фамилии не должна превышать 64 символов..',
//            'email.required' => 'Поле электронной почты обязательно.',
//            'email.min' => 'Поле электронной почты должна быть не менее 3-х символов.',
//            'email.max' => 'Поле электронной почты должна превышать 64 символов.',
            'email.unique' => 'Повторяющаяся запись для электронной почты',
            'password.required' => 'Поле пароля обязательно.',
            'city.required' => 'Поле Город обязательно.',
            'date_of_birth.required' => 'Поле Дата Рождения обязательно.',
            'password.min' => 'Пароль должен быть не менее 6 символов.',
            'password.max' => 'Пароль не должен быть длиннее 64 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password_confirmation.required' => 'Поле подтверждение пароля обязательно.',
        ];
    }

}
