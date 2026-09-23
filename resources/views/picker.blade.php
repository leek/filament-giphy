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
        <div class="fi-giphy-body">
            <div class="fi-input-wrp">
                <div class="fi-input-wrp-content-ctn">
                    <input
                        type="search"
                        x-model="query"
                        x-on:input="onQuery()"
                        placeholder="Search GIFs"
                        aria-label="Search GIFs"
                        class="fi-input"
                    />
                </div>
            </div>

            <p x-show="failed">GIPHY did not return GIFs. The document was not changed.</p>

            <div class="fi-giphy-results">
                <div class="fi-giphy-grid">
                    <template x-for="gif in gifs" :key="gif.id">
                        <button
                            type="button"
                            class="fi-giphy-result"
                            x-on:click="choose(gif)"
                            x-bind:disabled="! canInsert(gif)"
                            x-bind:aria-label="gif.title || 'GIF'"
                        >
                            <img
                                x-show="gridSrc(gif)"
                                x-bind:src="gridSrc(gif)"
                                x-bind:alt="gif.title || 'GIF'"
                                x-bind:width="gridWidth(gif)"
                                x-bind:height="gridHeight(gif)"
                                loading="lazy"
                            />
                        </button>
                    </template>
                </div>
            </div>

            <div class="fi-giphy-footer">
                <button
                    type="button"
                    class="fi-giphy-more"
                    x-show="hasMore()"
                    x-on:click="load(false)"
                    x-bind:disabled="loading"
                >
                    More GIFs
                </button>

                <span>Powered by GIPHY</span>
            </div>
        </div>
    </template>

    <script type="module" src="{{ $scriptUrl }}"></script>
</div>
