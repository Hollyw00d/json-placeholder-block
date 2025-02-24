export default function ResetButton({ resetBtnHandler }) {
	return (
		<p>
			<button type="button" onClick={resetBtnHandler}>
				Reset
			</button>
		</p>
	);
}
