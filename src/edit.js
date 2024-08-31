/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";
import { ToolbarButton, Button } from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";
import { useEntityProp } from "@wordpress/core-data";

import { addQueryArgs } from "@wordpress/url";
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, BlockControls } from "@wordpress/block-editor";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ setAttributes, attributes }) {
	const [title] = useEntityProp("postType", "post", "title");
	const [content] = useEntityProp("postType", "post", "content");
	const [link] = useEntityProp("postType", "post", "link");

	const fetchTweet = (e) => {
		e.preventDefault();
		console.log("submit");

		const params = {
			title: title,
			content: content,
			permalink: link,
		};

		console.log(params);

		apiFetch({
			path: addQueryArgs("/example-ai-block/v1/get-data", params),
		}).then((data) => {
			console.log(data);
			setAttributes({ tweet: data.tweet });
		});
	};

	return (
		<div {...useBlockProps()}>
			{attributes.tweet ? (
				<>
					<BlockControls>
						<ToolbarButton onClick={fetchTweet}>
							{__("Generate new tweet", "example-ai-block")}
						</ToolbarButton>
					</BlockControls>
					<div>
						<p>{attributes.tweet}</p>
						<a href="#">{__("Tweet this", "example-ai-block")}</a>
					</div>
				</>
			) : (
				<Button variant="secondary" onClick={fetchTweet}>
					{__("Click to generate a new tweet")}
				</Button>
			)}
		</div>
	);
}
