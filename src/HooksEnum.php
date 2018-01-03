<?php

namespace Makasim\Values;

class HooksEnum
{
    public const PRE_SET_VALUE = 'pre_set_value';
    public const PRE_ADD_VALUE = 'pre_add_value';
    public const POST_SET_VALUE = 'post_set_value';
    public const POST_SET_VALUES = 'post_set_values';
    public const POST_ADD_VALUE = 'post_add_values';
    public const POST_GET_VALUE = 'post_get_value';

    public const POST_SET_OBJECT = 'post_set_object';
    public const POST_ADD_OBJECT = 'post_add_object';
    public const POST_BUILD_OBJECT = 'post_build_object';
    public const POST_BUILD_SUB_OBJECT = 'post_build_sub_object';
    public const GET_OBJECT_CLASS = 'get_object_class';
    public const BUILD_OBJECT = 'build_object';
}
