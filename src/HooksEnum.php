<?php

namespace Formapro\Values;

class HooksEnum
{
    const PRE_SET_VALUE = 'pre_set_value';
    const PRE_ADD_VALUE = 'pre_add_value';
    const POST_SET_VALUE = 'post_set_value';
    const POST_SET_VALUES = 'post_set_values';
    const POST_ADD_VALUE = 'post_add_values';
    const POST_GET_VALUE = 'post_get_value';

    const POST_SET_OBJECT = 'post_set_object';
    const POST_ADD_OBJECT = 'post_add_object';
    const POST_BUILD_OBJECT = 'post_build_object';
    const POST_BUILD_SUB_OBJECT = 'post_build_sub_object';
    const GET_OBJECT_CLASS = 'get_object_class';
    const BUILD_OBJECT = 'build_object';
}
