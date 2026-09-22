<div
    class="fi-giphy-picker"
    x-data="{
        ready: false,
        bootPicker() {
            const start = () => {
                if (typeof window.filamentGiphyPicker !== 'function') {
                    setTimeout(start, 50)

                    return
                }

                Object.assign(this, window.filamentGiphyPicker(@js($config)))
                this.ready = true
                this.boot()
            }

            start()
        },
    }"
    x-init="bootPicker()"
>
    <p x-show="! ready">Loading the GIF picker.</p>

    <template x-if="ready">
        <div class="space-y-3">
            <input
                type="search"
                x-model="query"
                x-on:input="onQuery()"
                placeholder="Search GIFs"
                class="fi-input block w-full"
            />

            <p x-show="failed">GIPHY did not return GIFs. The document was not changed.</p>

            <div class="grid max-h-96 grid-cols-3 gap-2 overflow-y-auto">
                <template x-for="gif in gifs" :key="gif.id">
                    <button
                        type="button"
                        class="overflow-hidden rounded-lg bg-gray-950/5"
                        x-on:click="choose(gif)"
                        x-bind:disabled="! canInsert(gif)"
                    >
                        <img
                            x-show="gridSrc(gif)"
                            x-bind:src="gridSrc(gif)"
                            x-bind:alt="gif.title || 'GIF'"
                            class="h-28 w-full object-cover"
                        />
                    </button>
                </template>
            </div>

            <button
                type="button"
                class="text-sm underline"
                x-show="hasMore()"
                x-on:click="load(false)"
                x-bind:disabled="loading"
            >
                More GIFs
            </button>
        </div>
    </template>

    <p class="mt-3 text-xs">Powered by GIPHY</p>

    <script type="module" src="{{ $scriptUrl }}"></script>
</div>
