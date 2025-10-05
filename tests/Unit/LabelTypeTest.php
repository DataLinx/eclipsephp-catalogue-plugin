<?php

use Eclipse\Catalogue\Support\LabelType;

it('returns correct options for label types', function () {
    $options = LabelType::options();

    expect($options)->toBeArray();
    expect($options)->toHaveKeys(['gray', 'danger', 'success', 'warning', 'info', 'primary']);
    expect($options['gray'])->toBe('Gray');
    expect($options['danger'])->toBe('Danger');
    expect($options['success'])->toBe('Success');
    expect($options['warning'])->toBe('Warning');
    expect($options['info'])->toBe('Info');
    expect($options['primary'])->toBe('Primary');
});

it('returns correct badge class for each label type', function () {
    expect(LabelType::badgeClass('gray'))->toBe('fi-badge fi-color-gray');
    expect(LabelType::badgeClass('danger'))->toBe('fi-badge fi-color-danger');
    expect(LabelType::badgeClass('success'))->toBe('fi-badge fi-color-success');
    expect(LabelType::badgeClass('warning'))->toBe('fi-badge fi-color-warning');
    expect(LabelType::badgeClass('info'))->toBe('fi-badge fi-color-info');
    expect(LabelType::badgeClass('primary'))->toBe('fi-badge fi-color-primary');
});

it('returns options with expected colors', function () {
    $options = LabelType::options();

    expect($options)->toBeArray();
    expect($options)->toHaveKey('gray');
    expect($options)->toHaveKey('danger');
    expect($options)->toHaveKey('success');
    expect($options)->toHaveKey('warning');
    expect($options)->toHaveKey('info');
    expect($options)->toHaveKey('primary');
});
