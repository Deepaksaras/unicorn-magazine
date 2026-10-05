<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Purifier Profile
    |--------------------------------------------------------------------------
    |
    | This option specifies the default Purifier profile to use when cleaning
    | HTML content. You can define multiple profiles and switch between them.
    |
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Purifier Profiles
    |--------------------------------------------------------------------------
    |
    | Here you may configure the HTML purifier profiles for your application.
    | Each profile defines which HTML tags and attributes are allowed.
    |
    */

    'profiles' => [

        'default' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'p,b,strong,i,em,u,s,a[href|title],ul,ol,li,br,span,div,img[src|alt|width|height|style],h1,h2,h3,h4,h5,h6,blockquote,code,pre,table,thead,tbody,tr,th,td',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align,margin,margin-top,margin-right,margin-bottom,margin-left,padding,padding-top,padding-right,padding-bottom,padding-left,border,border-width,border-style,border-color,width,height',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
            'URI.AllowedSchemes' => [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                'tel' => true,
            ],
        ],

    ],

];
