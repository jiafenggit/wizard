@extends('layouts.project')
@section('page-content')
    @if($pageID !== 0)
        <div class="wz-panel-breadcrumb">
            <ol class="breadcrumb pull-left">
                <li><a href="/home">首页</a></li>
                <li><a href="{{ wzRoute('project:home', ['id' => $project->id]) }}">{{ $project->name }}</a></li>
                <li class="active">{{ $pageItem->title }}</li>
            </ol>
            <ul class="nav nav-pills pull-right">
                @can('page-edit', $pageItem)
                    <li role="presentation">
                        <a href="{{ wzRoute('project:doc:edit:show', ['id' => $project->id, 'page_id' => $pageItem->id]) }}" title="@lang('common.btn_edit')">
                            <span class="glyphicon glyphicon-edit"></span>
                        </a>
                    </li>
                    @if(!empty($history))
                        <li role="presentation">
                            <a href="#" wz-doc-compare-submit
                               data-doc1="{{ wzRoute('project:doc:json', ['id' => $project->id, 'page_id' => $pageItem->id]) }}"
                               data-doc2="{{ wzRoute('project:doc:history:json', ['history_id' => $history->id, 'id' => $project->id, 'page_id' => $pageItem->id]) }}"
                               title="@lang('common.btn_diff')">
                                <span class="glyphicon glyphicon-cutlery"></span>
                            </a>
                        </li>
                    @endif
                @endcan
                @include('components.page-menus', ['project' => $project, 'pageItem' => $pageItem])
            </ul>
            <div class="clearfix"></div>
        </div>
        <nav class="wz-page-control clearfix">
            <h1 class="wz-page-title">
                {{ $pageItem->title }}
                <span class="hide label label-{{ $type == 'swagger' ? 'success' : 'default' }}">{{ $type == 'swagger' ? 'sw' : 'md' }}</span>
            </h1>
        </nav>

        @include('components.document-info')

        <div class="markdown-body {{ $type == 'markdown' ? 'wz-markdown-style-fix' : '' }}" id="markdown-body">
            @if($type == 'markdown')
            <textarea id="append-test" style="display:none;">{{ $pageItem->content }}</textarea>
            @endif
        </div>

        @if(count($pageItem->attachments) > 0)
        <div class="wz-attachments">
            <hr />
            <h4>附件</h4>
            <ol>
                @foreach($pageItem->attachments as $attachment)
                    <li>
                        <a href="{{ $attachment->path }}">
                            <span class="glyphicon glyphicon-download-alt"></span>
                            {{ $attachment->name }}
                            <span class="wz-attachment-info">
                                【{{ $attachment->user->name }}，
                                {{ $attachment->created_at }}】
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
        @endif
    @else
        <h1>{{ $project->name or '' }}</h1>

        <p class="wz-document-header">@lang('document.document_create_info', ['username' => $project->user->name, 'time' => $project->created_at])</p>

        <p>{{ $project->description or '' }}</p>

        @if(!empty($operationLogs) && $operationLogs->count() > 0)
        <div class="wz-recently-log">
            <h4>最近活动</h4>
            <ul>
                @foreach($operationLogs as $log)
                <li>
                    <span class="wz-date">{{ $log->created_at }}</span>
                    @if ($log->message == 'document_updated')
                        <span class="wz-text-dashed">{{ $log->context->username }}</span> 修改了文档
                        <span class="wz-text-dashed"><a href="{{ wzRoute('project:home', ['id' => $project->id, 'p' => $log->context->doc_id]) }}">{{ $log->context->doc_title }}</a></span>
                        @if(!Auth::guest())
                        【<a href="#" wz-doc-compare-submit
                            data-doc1="{{ wzRoute('project:doc:json', ['id' => $project->id, 'page_id' => $log->context->doc_id]) }}"
                            data-doc2="{{ wzRoute('project:doc:history:json', ['history_id' => $log->context->history_id ?? 0, 'id' => $project->id, 'page_id' => $log->context->doc_id]) }}">@lang('common.btn_diff')</a>】
                        @endif
                    @elseif ($log->message == 'document_created')
                        <span class="wz-text-dashed">{{ $log->context->username }}</span> 创建了文档
                        <span class="wz-text-dashed"><a href="{{ wzRoute('project:home', ['id' => $project->id, 'p' => $log->context->doc_id]) }}">{{ $log->context->doc_title }}</a></span>
                    @elseif ($log->message == 'document_deleted')
                        <span class="wz-text-dashed">{{ $log->context->username }}</span> 删除了文档
                        <span class="wz-text-dashed"><a href="{{ wzRoute('project:home', ['id' => $project->id, 'p' => $log->context->doc_id]) }}">{{ $log->context->doc_title }}</a></span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @if($project->groups->count() > 0)
            <div class="wz-group-allowed-list">
                <h4>@lang('project.group_added')</h4>
                <table class="table">
                    <caption></caption>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('project.group_name')</th>
                        <th>@lang('project.group_write_enabled')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($project->groups as $group)
                        <tr>
                            <th scope="row">{{ $group->id }}</th>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->projects[0]->pivot->privilege == 1 ? __('common.yes') : __('common.no') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
@endsection

@includeIf("components.{$type}-show")

@push('page-panel')
    @if($pageID != 0 && !(Auth::guest() && count($pageItem->comments) === 0))
        @include('components.comment')
    @endif

    @if(!Auth::guest())
        @include('components.doc-compare-script')
    @endif
@endpush