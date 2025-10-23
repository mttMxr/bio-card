<?php use App\Models\UserData; ?>

@php
    $ShowShrBtn = config('advanced-config.display_share_button');

    if ($ShowShrBtn === 'false') {
        $ShowShrBtn = 'false';
    } elseif ($ShowShrBtn === 'user') {
        $ShowShrBtn = Auth::user()->littlelink_name ? 'true' : 'false';
    } elseif (UserData::getData($userinfo->id, 'disable-sharebtn') == "true") {
        $ShowShrBtn = 'false';
    } else {
        $ShowShrBtn = 'true';
    }

@endphp

<div align="right" @if($ShowShrBtn == 'false') style="visibility:hidden" @endif class="sharediv">
  <div>
    <span class="sharebutton button-hover icon-hover share-button" data-share="{{url()->current()}}" tabindex="0" role="button" aria-label="{{__('messages.Share this page')}}">
      <i style="color: black;" class="fa-solid fa-share sharebutton-img share-icon hvr-icon"></i>
      <span class="sharebutton-mb">{{__('messages.Share')}}</span>
    </span>
  </div>
</div>
<span class="copy-icon" tabindex="0" role="button" aria-label="{{__('messages.Copy URL to clipboard')}}"></span>

@if($ShowShrBtn == 'true')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll(".share-button");

    shareButtons.forEach(function(button) {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            event.stopPropagation();

            const shareUrl = button.dataset.share;

            // Check if Web Share API is available and secure context
            if (navigator.share && window.isSecureContext) {
                navigator.share({
                    title: "{{__('messages.Share this page')}}",
                    url: shareUrl
                }).catch(function(error) {
                    console.error("Share error:", error);
                    // Fallback to clipboard if share fails
                    copyToClipboard(shareUrl);
                });
            } else {
                // Fallback to clipboard copy
                copyToClipboard(shareUrl);
            }
        });

        // Also handle keyboard accessibility
        button.addEventListener("keypress", function(event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                button.click();
            }
        });
    });

    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                alert("{{__('messages.URL has been copied to your clipboard!')}}");
            }).catch(function(error) {
                console.error("Clipboard error:", error);
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        // Fallback method for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert("{{__('messages.URL has been copied to your clipboard!')}}");
        } catch (error) {
            console.error("Fallback copy error:", error);
            alert("{{__('messages.Could not copy URL. Please copy manually:')}}\n" + text);
        }
        document.body.removeChild(textArea);
    }
});
</script>
@endif