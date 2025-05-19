<nav aria-label="pagination">
    <ul class="flex shrink-0 items-center gap-2 text-sm font-medium">
        <li>
            <button
                class="flex items-center cursor-pointer rounded-radius p-1 text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark {{ $data->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                aria-label="previous page"
                @if(!$data->onFirstPage()) wire:click="previousPage" @endif
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     aria-hidden="true" class="size-6">
                    <path fill-rule="evenodd"
                          d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                          clip-rule="evenodd"/>
                </svg>
                Previous
            </button>
        </li>

        <li>
            <button
                class="flex cursor-pointer size-6 w-fit items-center justify-center rounded-radius p-1 {{ 1 == $data->currentPage() ? 'bg-primary font-bold text-on-primary dark:bg-primary-dark dark:text-on-primary-dark' : 'text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark' }}"
                aria-label="page 1"
                @if(1 != $data->currentPage()) wire:click="gotoPage(1)" @endif
                @if(1 == $data->currentPage()) aria-current="page" @endif
            >1
            </button>
        </li>

        @if ($data->currentPage() > $options->paginationPages + 2)
            <li>
                <button
                    class="flex size-6 cursor-pointer w-fit items-center justify-center rounded-radius p-1 text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark"
                    aria-label="more pages" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" aria-hidden="true" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                </button>
            </li>
        @endif

        @for ($page = max(2, $data->currentPage() - $options->paginationPages); $page <= min($data->lastPage() - 1, $data->currentPage() + $options->paginationPages); $page++)
            <li>
                <button
                    class="flex size-6 w-fit cursor-pointer items-center justify-center rounded-radius p-1 {{ $page == $data->currentPage() ? 'bg-primary font-bold text-on-primary dark:bg-primary-dark dark:text-on-primary-dark' : 'text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark' }}"
                    aria-label="page {{ $page }}"
                    @if($page != $data->currentPage()) wire:click="gotoPage({{ $page }})" @endif
                    @if($page == $data->currentPage()) aria-current="page" @endif
                >{{ $page }}</button>
            </li>
        @endfor

        @if ($data->currentPage() < $data->lastPage() - $options->paginationPages - 1)
            <li>
                <button
                    class="flex size-6 cursor-pointer w-fit items-center justify-center rounded-radius p-1 text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark"
                    aria-label="more pages" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" aria-hidden="true" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                </button>
            </li>
        @endif

        @if ($data->lastPage() > 1)
            <li>
                <button
                    class="flex size-6 cursor-pointer w-fit items-center justify-center rounded-radius p-1 {{ $data->lastPage() == $data->currentPage() ? 'bg-primary font-bold text-on-primary dark:bg-primary-dark dark:text-on-primary-dark' : 'text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark' }}"
                    aria-label="page {{ $data->lastPage() }}"
                    @if($data->lastPage() != $data->currentPage()) wire:click="gotoPage({{ $data->lastPage() }})"
                    @endif
                    @if($data->lastPage() == $data->currentPage()) aria-current="page" @endif
                >{{ $data->lastPage() }}</button>
            </li>
        @endif

        <li>
            <button
                class="flex items-center cursor-pointer rounded-radius p-1 text-on-surface hover:text-primary dark:text-on-surface-dark dark:hover:text-primary-dark {{ $data->onLastPage() ? 'opacity-50 pointer-events-none' : '' }}"
                aria-label="next page"
                @if(!$data->onLastPage()) wire:click="nextPage" @endif
            >
                Next
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     aria-hidden="true" class="size-6">
                    <path fill-rule="evenodd"
                          d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd"/>
                </svg>
            </button>
        </li>
    </ul>
</nav>
